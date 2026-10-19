<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProfileUpdateRequest;
use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use App\Models\LoginToken;
use App\Models\ProfileFact;
use App\Services\EmbeddingService;
use App\Services\GameService;
use App\Services\MatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Show the login page (unauthenticated entry point).
     */
    public function login(): View
    {
        return view('account.login');
    }

    /**
     * Receive @username, generate token, send magic link via bot API.
     */
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'regex:/^[a-zA-Z0-9_]{3,32}$/'],
        ], [
            'username.required' => 'Введите Telegram username.',
            'username.regex'    => 'Username должен содержать только буквы, цифры и _. Без @.',
        ]);

        $username = ltrim($request->input('username'), '@');

        $user = BotUser::where('telegram_username', $username)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        // Always show success to prevent username enumeration
        if (! $user) {
            return redirect()->route('account.login')->with('sent', true);
        }

        $token    = LoginToken::generateFor((int) $user->telegram_id);
        $url      = url('/go/' . substr($token->token, 0, 8));
        $firstName = explode(' ', (string) $user->full_name)[0];

        Http::post('https://api.telegram.org/bot' . config('nutgram.token') . '/sendMessage', [
            'chat_id'      => $user->telegram_id,
            'text'         => "Привет, {$firstName}! Нажми кнопку ниже, чтобы войти в личный кабинет.\n\n⏱ Ссылка действует 24 часа.",
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => '🔐 Войти в кабинет →', 'url' => $url],
                ]],
            ]),
        ]);

        return redirect()->route('account.login')->with('sent', true);
    }

    /**
     * Validate the magic token and start a session.
     */
    public function auth(Request $request): RedirectResponse
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return redirect()->route('account.login')
                ->with('error', 'Ссылка недействительна.');
        }

        $loginToken = LoginToken::where('token', $token)->first();

        if (! $loginToken || ! $loginToken->isValid()) {
            return redirect()->route('account.login')
                ->with('error', 'Ссылка истекла. Запросите новую через бот командой /login.');
        }

        $user = BotUser::where('telegram_id', $loginToken->telegram_id)
            ->where('status', BotUser::STATUS_APPROVED)
            ->first();

        if (! $user) {
            return redirect()->route('account.login')
                ->with('error', 'Доступ закрыт. Ваша заявка ещё не одобрена или была отозвана.');
        }

        // Start session (7 days)
        $request->session()->regenerate();
        session(['account_telegram_id' => $user->telegram_id]);
        session()->put('_account_expires', now()->addDays(7)->timestamp);

        return redirect()->route($request->query('to') === 'game' && app(GameService::class)->isEnabled() ? 'account.game' : 'account.index');
    }

    /**
     * Dashboard / home page.
     */
    public function index(): View
    {
        return view('account.index');
    }

    /**
     * Show profile view (read-only).
     */
    public function profile(): View
    {
        return view('account.profile');
    }

    /**
     * Show profile edit form.
     */
    public function profileEdit(): View
    {
        return view('account.profile-edit');
    }

    /**
     * Update profile.
     */
    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $gameOn = app(GameService::class)->isEnabled();

        $user->update([
            'full_name'       => $request->validated('full_name'),
            'description'     => $request->validated('description'),
            'expectation'     => $request->validated('expectation'),
            'contacts_closed' => $request->boolean('contacts_closed'),
        ] + ($gameOn ? [
            // эти поля есть в форме только при включённой игре: иначе не трогаем сохранённые значения
            'quests_opt_out'      => $request->boolean('quests_opt_out'),
            'notifications_muted' => $request->boolean('notifications_muted'),
        ] : []));

        if ($gameOn) {
            $this->syncFacts($user, (array) $request->input('facts', []), (array) $request->input('new_facts', []));
            app(GameService::class)->syncProfileRewards($user->fresh());
        }

        // Recompute embedding since profile text changed
        ComputeUserEmbedding::dispatch($user);

        return redirect()->route('account.profile')
            ->with('success', 'Профиль обновлён.');
    }

    /**
     * AI matches page.
     */
    public function matches(MatchingService $matcher): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $matches = $matcher->topMatches($accountUser);

        return view('account.matches', compact('matches'));
    }

    /**
     * Community members page.
     */
    public function people(): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $people = BotUser::approved()
            ->where('telegram_id', '!=', $accountUser->telegram_id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'telegram_username', 'description', 'expectation', 'avatar_path', 'contacts_closed', 'region']);

        return view('account.people', compact('people'));
    }

    /**
     * Show a single community member's public profile.
     */
    public function showPerson(BotUser $botUser): View|RedirectResponse
    {
        abort_if($botUser->status !== BotUser::STATUS_APPROVED, 404);

        /** @var BotUser $viewer */
        $viewer = view()->shared('accountUser');
        $game   = app(GameService::class);

        // Раздел «Люди» закрыт уровнем, но люди из своей сети всегда доступны
        if ($viewer->id !== $botUser->id && ! $game->canAccess($viewer, 'people')) {
            $inNetwork = \App\Models\Connection::where(fn ($q) => $q->where('user_id', $viewer->id)->where('target_id', $botUser->id))
                ->orWhere(fn ($q) => $q->where('user_id', $botUser->id)->where('target_id', $viewer->id)->where('mutual', true))
                ->exists();
            if (! $inNetwork) {
                return redirect()->route('account.game', ['locked' => 'people']);
            }
        }

        $botUser->load('facts');

        return view('account.person', ['person' => $botUser]);
    }

    /**
     * AI search page — find people by arbitrary text query.
     */
    public function search(Request $request, EmbeddingService $embedder, MatchingService $matcher): View
    {
        /** @var BotUser $accountUser */
        $accountUser = view()->shared('accountUser');

        $query   = trim((string) $request->query('q', ''));
        $results = null;

        if ($query !== '') {
            $request->validate(['q' => ['string', 'max:500']]);

            try {
                $vector  = $embedder->embed($query);
                $results = $matcher->searchByQuery($vector, $accountUser);
            } catch (\Throwable $e) {
                logger()->warning('AI search failed', ['error' => $e->getMessage()]);
                $results = collect();
            }
        }

        return view('account.search', compact('query', 'results'));
    }

    /**
     * Knowledge base / resources page.
     */
    public function knowledge(): View
    {
        return view('account.knowledge');
    }

    /**
     * Delete account and clear session.
     */
    public function deleteProfile(Request $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        // Remove the bot keyboard from the user's Telegram chat
        Http::post('https://api.telegram.org/bot' . config('nutgram.token') . '/sendMessage', [
            'chat_id'      => $user->telegram_id,
            'text'         => "✅ Ваш профиль удалён.\n\nСпасибо, что были с нами — будем рады видеть вас снова 🙏",
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
                'inline_keyboard' => [[
                    ['text' => '↩️ Вернуться', 'callback_data' => 'restart'],
                ]],
            ]),
        ]);

        $user->delete();

        session()->forget('account_telegram_id');
        session()->forget('_account_expires');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login')
            ->with('success', 'Ваш профиль удалён.');
    }

    /**
     * Destroy session and log out.
     */
    public function logout(Request $request): RedirectResponse
    {
        session()->forget('account_telegram_id');
        session()->forget('_account_expires');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login', ['logout' => '1']);
    }

    /**
     * Existing facts are edited by id (cleared = deleted); new ones are appended up to the limit.
     *
     * @param array<int|string, mixed> $existing
     * @param array<int, mixed>        $added
     */
    private function syncFacts(BotUser $user, array $existing, array $added): void
    {
        $max = (int) config('game.max_facts');

        foreach ($user->facts as $fact) {
            if (! array_key_exists($fact->id, $existing)) {
                continue;
            }
            $body = trim((string) $existing[$fact->id]);
            if ($body === '') {
                $fact->delete();
            } elseif ($body !== $fact->body) {
                $fact->update(['body' => $body]);
            }
        }

        $count = $user->facts()->count();
        foreach ($added as $body) {
            $body = trim((string) $body);
            if ($body === '' || $count >= $max) {
                continue;
            }
            ProfileFact::create(['bot_user_id' => $user->id, 'body' => $body]);
            $count++;
        }
    }
}
