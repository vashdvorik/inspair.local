<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProfileUpdateRequest;
use App\Models\BotUser;
use App\Models\LoginToken;
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

        return redirect()->route('account.index');
    }

    /**
     * Dashboard / home page.
     */
    public function index(): View
    {
        return view('account.index');
    }

    /**
     * Show profile edit form.
     */
    public function profile(): View
    {
        return view('account.profile');
    }

    /**
     * Update profile.
     */
    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = view()->shared('accountUser');
        $user->update($request->validated());

        return redirect()->route('account.profile')
            ->with('success', 'Профиль обновлён.');
    }

    /**
     * AI matches page (placeholder).
     */
    public function matches(): View
    {
        return view('account.matches');
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
            ->get(['id', 'full_name', 'telegram_username', 'description']);

        return view('account.people', compact('people'));
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
            'reply_markup' => json_encode(['remove_keyboard' => true]),
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
}
