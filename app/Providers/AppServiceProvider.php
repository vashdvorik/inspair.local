<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use App\Support\PublicUi;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Домен берётся только из APP_URL: на https-домене все сгенерированные ссылки тоже https
        if (str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Public site: helpers available in every view under resources/views/public
        View::composer('public.*', function ($view): void {
            $view->with([
                't'     => fn (string $ru, string $en, string $ro) => PublicUi::span(compact('ru', 'en', 'ro')),
                'p'     => fn (string $ru, string $en, string $ro, string $class = '') => PublicUi::paras(compact('ru', 'en', 'ro'), $class),
                'tm'    => fn (array $v) => PublicUi::span($v),
                'pm'    => fn (array $v, string $class = '') => PublicUi::paras($v, $class),
                'tk'    => fn (array $T, string $path) => PublicUi::path($T, $path),
                'pk'    => fn (array $T, string $path, string $class = '') => PublicUi::path($T, $path, true, $class),
                'icon'  => fn (string $k, string $cls = 'w-6 h-6') => PublicUi::icon($k, $cls),
                'img'   => fn (string $rel) => asset('img/'.$rel),
                'initials' => fn (string $n) => PublicUi::initials($n),
                'tg'    => PublicUi::telegramIcon(),
                'arrow' => PublicUi::arrow(),
                'bot'   => PublicUi::BOT,
            ]);
        });

        // 20 auth attempts per minute per IP — prevents brute-force on tokens
        RateLimiter::for('20,1', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
