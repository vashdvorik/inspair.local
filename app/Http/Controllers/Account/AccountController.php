<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProfileUpdateRequest;
use App\Jobs\ComputeUserEmbedding;
use App\Models\BotUser;
use App\Models\LoginToken;
use App\Services\EmbeddingService;
use App\Services\GameService;
use App\Services\MatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'username.required' => \App\Support\Lang::t('Введите Telegram username.', 'Enter your Telegram username.', 'Introdu username-ul de Telegram.'),
            'username.regex'    => \App\Support\Lang::t('Username должен содержать только буквы, цифры и _. Без @.', 'The username may contain only letters, digits and _. No @.', 'Username-ul poate conține doar litere, cifre și _. Fără @.'),
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
            'text'         => str_replace('{NAME}', (string) $firstName, \App\Support\Lang::t('Привет, {NAME}! Нажми кнопку ниже, чтобы войти в личный кабинет.', 'Hi, {NAME}! Press the button below to sign in to your account.', 'Salut, {NAME}! Apasă butonul de mai jos ca să intri în cont.')) . "\n\n⏱ " . \App\Support\Lang::t('Ссылка действует 24 часа.', 'The link is valid for 24 hours.', 'Linkul este valabil 24 de ore.'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => '🔐 ' . \App\Support\Lang::t('Войти в кабинет →', 'Sign in →', 'Intră în cont →'), 'url' => $url],
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
                ->with('error', \App\Support\Lang::t('Ссылка недействительна.', 'The link is not valid.', 'Linkul nu este valid.'));
        }

        $loginToken = LoginToken::where('token', $token)->first();

        if (! $loginToken || ! $loginToken->isValid()) {
            return redirect()->route('account.login')
                ->with('error', \App\Support\Lang::t('Ссылка истекла. Запросите новую через бот командой /login.', 'The link has expired. Request a new one in the bot with /login.', 'Linkul a expirat. Cere unul nou în bot cu comanda /login.'));
        }

        $user = BotUser::where('telegram_id', $loginToken->telegram_id)->first();

        if (! $user) {
            return redirect()->route('account.login')
                ->with('error', \App\Support\Lang::t('Вы ещё не зарегистрированы. Нажмите «Старт» в боте и ответьте на его вопросы.', 'You are not registered yet. Press “Start” in the bot and answer its questions.', 'Nu ești înregistrat încă. Apasă „Start” în bot și răspunde la întrebările lui.'));
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
    public function index(GameService $game): View
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');

        // что ещё не заполнено в профиле: из этого собирается индикатор и подсказка. Факты о себе здесь не просим:
        // их участник пишет уже в игре
        $steps = [
            'avatar'      => (bool) $user->avatar_path,
            'description' => mb_strlen(trim((string) $user->description)) >= 20,
        ];
        $completeness = (int) round(count(array_filter($steps)) / count($steps) * 100);

        // новые лица сообщества (кэш на 2 минуты: главная открывается чаще всех страниц)
        $community = Cache::remember('cabinet:home:community', 120, fn () => [
            
            'latest' => BotUser::approved()->latest('approved_at')->limit(9)->get(['id', 'full_name', 'avatar_path', 'description', 'updated_at']),
        ]);

        return view('account.index', [
            'completeness' => $completeness,
            'steps'        => $steps,
            'newMembers'   => $community['latest']->where('id', '!=', $user->id)->take(8)->values(),
            'opportunities' => $this->freshOpportunities(),
            'opportunitiesTotal' => count(\App\Support\OpportunityFeed::all()),
            'progress'     => $game->isEnabled() ? $game->progress($user) : null,
        ]);
    }

    /** Для главной: возможности, на которые ещё можно подать заявку (или без срока). Если таких нет, просто последние.
     *
     * @return list<array<string, mixed>>
     */
    private function freshOpportunities(int $limit = 3): array
    {
        $all = \App\Support\OpportunityFeed::all();
        $open = array_values(array_filter($all, fn (array $o) => empty($o['deadline']) || \Illuminate\Support\Carbon::parse($o['deadline'])->endOfDay()->isFuture()));

        return array_slice($open ?: $all, 0, $limit);
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
            'organization'    => $request->validated('organization') ?: null,
            'city'            => $request->validated('city') ?: null,
            'description'     => $request->validated('description'),
            'contacts_closed' => $request->boolean('contacts_closed'),
        ] + ($request->has('notifications') ? [
            // галочка «Получать новости и возможности в боте»; без поля в запросе настройка не меняется
            'notifications_muted' => ! $request->boolean('notifications'),
        ] : []) + [
            'contact_phone'     => $request->validated('contact_phone') ?: null,
            'contact_email'     => $request->validated('contact_email') ?: null,
        ] + ($gameOn ? [
            // эти поля есть в форме только при включённой игре: иначе не трогаем сохранённые значения
            'quests_opt_out'      => $request->boolean('quests_opt_out'),
        ] : []));

        if ($gameOn) {
            app(GameService::class)->syncProfileRewards($user->fresh());
        }

        // Recompute embedding since profile text changed
        ComputeUserEmbedding::dispatch($user);

        return redirect()->route('account.profile')
            ->with('success', \App\Support\Lang::t('Профиль обновлён.', 'Profile updated.', 'Profil actualizat.'));
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
            ->get(['id', 'full_name', 'telegram_username', 'description', 'expectation', 'avatar_path', 'contacts_closed']);

        return view('account.people', compact('people'));
    }

    /**
     * Show a single community member's public profile.
     */
    public function showPerson(BotUser $botUser): View|RedirectResponse
    {
        abort_if($botUser->status !== BotUser::STATUS_APPROVED, 404);

        /** @var BotUser $viewer */
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
}
