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
    return view('public.home', [
        'site'     => PublicUi::content('site'),
        'c23'      => PublicUi::content('academy2023'),
        'people'   => PublicUi::content('people'),
        'partners' => PublicUi::content('partners'),
        'videos'   => PublicUi::content('videos'),
        'gallery'  => PublicUi::content('gallery'),
    ]);
})->name('home');
Route::get('/experts', fn () => view('public.experts', ['people' => PublicUi::content('people')]))->name('experts');
Route::get('/partners', fn () => view('public.partners', ['partners' => PublicUi::content('partners')]))->name('partners');
Route::redirect('/participants', '/experts', 301);

// Events: Hackathon 2026, Schools 2025, School 2023
Route::get('/events/hackathon-2026', fn () => view('public.hackathon', ['site' => PublicUi::content('site')]))->name('hackathon');
Route::get('/events/schools-2025', function () {
    return view('public.academy2025.index', [
        'c'        => PublicUi::content('academy2025'),
        'site'     => PublicUi::content('site'),
        'people'   => PublicUi::content('people'),
        'partners' => PublicUi::content('partners'),
        'videos'   => PublicUi::content('videos'),
        'gallery'  => PublicUi::content('gallery'),
    ]);
})->name('academy2025');
Route::get('/events/schools-2025/school-{n}', function (int $n) {
    abort_unless(in_array($n, [1, 2, 3], true), 404);

    return view('public.academy2025.school', ['c' => PublicUi::content('academy2025'), 'site' => PublicUi::content('site'), 'n' => $n]);
})->whereNumber('n')->name('academy2025.school');
Route::get('/events/school-2023', function () {
    return view('public.academy2023.index', [
        'c'        => PublicUi::content('academy2023'),
        'site'     => PublicUi::content('site'),
        'people'   => PublicUi::content('people'),
        'partners' => PublicUi::content('partners'),
        'gallery'  => PublicUi::content('gallery'),
    ]);
})->name('academy2023');
// previous addresses keep working
Route::redirect('/academy-2025', '/events/schools-2025', 301);
Route::redirect('/academy-2025/school-{n}', '/events/schools-2025/school-{n}', 301)->where('n', '[1-3]');
Route::redirect('/academy-2023', '/events/school-2023', 301);

Route::get('/photo', fn () => view('public.photo', ['gallery' => PublicUi::content('gallery')]))->name('photo');
Route::get('/video', fn () => view('public.video', ['videos' => PublicUi::content('videos')]))->name('video');
Route::get('/publications', fn () => view('public.publications', ['site' => PublicUi::content('site')]))->name('publications');
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

