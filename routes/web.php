<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\GameController;
use App\Http\Controllers\Account\OpportunityController;
use App\Http\Controllers\Account\TmaAuthController;
use App\Http\Middleware\RequireAccountAuth;
use App\Models\LoginToken;
use App\Support\PublicUi;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

// ── Public site ───────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('public.about', [
        'c25'      => PublicUi::content('academy2025'),
        'c23'      => PublicUi::content('academy2023'),
        'people'   => PublicUi::content('people'),
        'partners' => PublicUi::content('partners'),
        'videos'   => PublicUi::content('videos'),
        'photos'   => PublicUi::content('photos'),
    ]);
})->name('home');
Route::get('/experts', fn () => view('public.experts', ['people' => PublicUi::content('people')]))->name('experts');
Route::get('/partners', fn () => view('public.partners', ['partners' => PublicUi::content('partners')]))->name('partners');
Route::redirect('/participants', '/experts', 301);
Route::get('/academy-2025', function () {
    return view('public.academy2025.index', [
        'c'        => PublicUi::content('academy2025'),
        'people'   => PublicUi::content('people'),
        'partners' => PublicUi::content('partners'),
        'videos'   => PublicUi::content('videos'),
    ]);
})->name('academy2025');
Route::get('/academy-2025/school-{n}', function (int $n) {
    abort_unless(in_array($n, [1, 2, 3], true), 404);

    return view('public.academy2025.school', ['c' => PublicUi::content('academy2025'), 'n' => $n]);
})->whereNumber('n')->name('academy2025.school');
Route::get('/academy-2023', function () {
    return view('public.academy2023.index', [
        'c'        => PublicUi::content('academy2023'),
        'people'   => PublicUi::content('people'),
        'partners' => PublicUi::content('partners'),
        'photos'   => PublicUi::content('photos'),
    ]);
})->name('academy2023');

// Telegram Webhook — POST запрос от серверов Telegram
Route::post('/telegram/webhook', function (Nutgram $bot) {
    $bot->run();
})->name('telegram.webhook');

// Account: magic-link auth (no middleware)
Route::get('/app/account/auth', [AccountController::class, 'auth'])->middleware('throttle:20,1')->name('account.auth');
Route::get('/app/account/login', [AccountController::class, 'login'])->name('account.login');
Route::post('/app/account/tma-auth', [TmaAuthController::class, 'auth'])->middleware('throttle:20,1')->name('account.tma-auth');

// Short-link redirect: /go/{code} — hides the full token from Telegram dialog
Route::get('/go/{code}', function (string $code) {
    $token = LoginToken::where('token', 'like', $code . '%')->first();

    if (! $token || ! $token->isValid()) {
        return redirect()->route('account.login')->with('error', 'Ссылка недействительна или истекла.');
    }

    return redirect()->route('account.auth', ['token' => $token->token] + (request('to') === 'game' ? ['to' => 'game'] : []));
})->middleware('throttle:20,1')->where('code', '[0-9a-f]{8}')->name('account.go');

// Account: protected cabinet
Route::middleware(RequireAccountAuth::class)
    ->prefix('app/account')
    ->name('account.')
    ->group(function (): void {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/profile/edit', [AccountController::class, 'profileEdit'])->name('profile.edit');
        Route::post('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::delete('/profile', [AccountController::class, 'deleteProfile'])->name('profile.delete');
        Route::get('/matches', [AccountController::class, 'matches'])->middleware('game.access:matches')->name('matches');
        Route::get('/people', [AccountController::class, 'people'])->middleware('game.access:people')->name('people');
        Route::get('/people/{botUser}', [AccountController::class, 'showPerson'])->name('people.show');
        Route::get('/search', [AccountController::class, 'search'])->middleware('game.access:search')->name('search');

        // Игра: страница (Vue) и JSON-API
        Route::get('/game', [GameController::class, 'index'])->middleware('game.on')->name('game');
        Route::prefix('game/api')->middleware('game.on')->name('game.api.')->group(function (): void {
            Route::get('/state', [GameController::class, 'state'])->name('state');
            Route::get('/quest', [GameController::class, 'quest'])->name('quest');
            Route::post('/quest/{quest}/answer', [GameController::class, 'answer'])->middleware('throttle:60,1')->name('answer');
            Route::post('/quest/{quest}/report', [GameController::class, 'report'])->middleware('throttle:20,1')->name('report');
            Route::get('/network', [GameController::class, 'network'])->name('network');
            Route::post('/connections/{botUser}/reciprocate', [GameController::class, 'reciprocate'])->name('reciprocate');
        });
        Route::get('/knowledge', [AccountController::class, 'knowledge'])->name('knowledge');
        Route::get('opportunities/create', [OpportunityController::class, 'create'])->middleware('game.access:opportunities')->name('opportunities.create');
        Route::post('opportunities', [OpportunityController::class, 'store'])->middleware('game.access:opportunities')->name('opportunities.store');
        Route::resource('opportunities', OpportunityController::class)->only(['index', 'destroy']);
        Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
    });

