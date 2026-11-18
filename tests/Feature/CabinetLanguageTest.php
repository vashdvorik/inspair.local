<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BotUser;
use App\Support\Lang;
use Tests\TestCase;

/** Кабинет и страница входа на трёх языках: ru / en / ro. */
class CabinetLanguageTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_login_page_in_each_language(): void
    {
        foreach (['ru' => 'Ответьте на вопросы бота', 'en' => 'Answer the bot', 'ro' => 'Răspunde la întrebările botului'] as $code => $phrase) {
            $this->withUnencryptedCookie(Lang::COOKIE, $code)->get(route('account.login'))
                ->assertOk()->assertSee($phrase)->assertSee('<html lang="'.$code.'"', false);
        }
    }

    public function test_language_follows_the_browser_when_no_choice_made(): void
    {
        $this->withHeaders(['Accept-Language' => 'ro-RO,ro;q=0.9,en;q=0.8'])->withUnencryptedCookie(Lang::COOKIE, '')
            ->get(route('account.login'))->assertSee('<html lang="ro"', false);
        $this->withHeaders(['Accept-Language' => 'de,fr'])->withUnencryptedCookie(Lang::COOKIE, '')
            ->get(route('account.login'))->assertSee('<html lang="ru"', false);
    }

    public function test_switcher_sets_cookie_and_returns_to_the_same_page(): void
    {
        $response = $this->from(route('account.login'))->get(route('lang', 'ro'));

        $response->assertRedirect(route('account.login'));
        $response->assertCookie(Lang::COOKIE, 'ro', false);
        $this->get(route('lang', 'xx'))->assertNotFound();
    }

    public function test_switcher_never_redirects_to_another_site(): void
    {
        $this->from('https://evil.example/page')->get(route('lang', 'en'))->assertRedirect('/');
    }

    public function test_switcher_works_without_a_referer_because_the_page_is_in_the_link(): void
    {
        // в Telegram Referer часто нет: раньше это выкидывало на главную сайта
        $this->get(route('lang', ['code' => 'ro', 'to' => '/app/account/people?x=1']))
            ->assertRedirect('/app/account/people?x=1')
            ->assertCookie(Lang::COOKIE, 'ro', false)
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_return_address_must_be_a_local_path(): void
    {
        foreach (['https://evil.example/x', '//evil.example/x', 'javascript:alert(1)', '/\\evil.example', "/ok\r\nSet-Cookie: a=b"] as $bad) {
            $this->get(route('lang', ['code' => 'en', 'to' => $bad]))->assertRedirect('/');
        }
    }

    public function test_language_cookie_survives_inside_telegram_web_iframe(): void
    {
        // внутри iframe (Telegram Web) кука с SameSite=Lax не сохраняется: на https она ставится как None; Secure; Partitioned
        $response = $this->call('GET', 'https://inspire.test/lang/ro', ['to' => '/app/account']);
        $header = strtolower(implode(' | ', $response->headers->all('set-cookie')));

        $this->assertStringContainsString(strtolower(Lang::COOKIE).'=ro', $header);
        $this->assertStringContainsString('samesite=none', $header);
        $this->assertStringContainsString('secure', $header);
        $this->assertStringContainsString('partitioned', $header);
    }

    public function test_first_telegram_login_takes_the_language_from_telegram_but_keeps_a_manual_choice(): void
    {
        config(['nutgram.token' => 'TEST:TOKEN']);
        $u = BotUser::create(['telegram_id' => 777004, 'telegram_username' => 'tg_lang', 'full_name' => 'Tg Lang', 'status' => BotUser::STATUS_APPROVED]);

        $initData = function (string $langCode) use ($u): string {
            $params = ['auth_date' => (string) time(), 'user' => json_encode(['id' => $u->telegram_id, 'language_code' => $langCode])];
            ksort($params);
            $check = implode("\n", array_map(fn ($k, $v) => "$k=$v", array_keys($params), array_values($params)));
            $params['hash'] = hash_hmac('sha256', $check, hash_hmac('sha256', 'TEST:TOKEN', 'WebAppData', true));

            return http_build_query($params);
        };

        $this->postJson(route('account.tma-auth'), ['init_data' => $initData('ro')])->assertOk()->assertCookie(Lang::COOKIE, 'ro', false);

        // язык, который человек выбрал сам, не перезаписывается языком Telegram
        $this->withCredentials()->withUnencryptedCookie(Lang::COOKIE, 'en')->postJson(route('account.tma-auth'), ['init_data' => $initData('ro')])
            ->assertOk()->assertCookieMissing(Lang::COOKIE);

        // язык, которого у кабинета нет (например, немецкий), ничего не ставит
        $this->postJson(route('account.tma-auth'), ['init_data' => $initData('de')])->assertOk()->assertCookieMissing(Lang::COOKIE);
    }

    public function test_cabinet_switcher_links_carry_the_current_page(): void
    {
        $u = BotUser::create(['telegram_id' => 777003, 'telegram_username' => 'lang_link', 'full_name' => 'Lang Link', 'status' => BotUser::STATUS_APPROVED]);
        $html = $this->withSession(['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp])
            ->get(route('account.people'))->getContent();

        $this->assertStringContainsString('to=%2Fapp%2Faccount%2Fpeople', $html);
    }

    public function test_cabinet_pages_are_translated(): void
    {
        $u = BotUser::create(['telegram_id' => 777001, 'telegram_username' => 'lang_test', 'full_name' => 'Lang Test', 'status' => BotUser::STATUS_APPROVED]);
        $session = ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];

        $this->withSession($session)->withUnencryptedCookie(Lang::COOKIE, 'en')->get(route('account.index'))
            ->assertOk()->assertSee('New opportunities')->assertSee('Log out')->assertDontSee('Выйти');
        $this->withSession($session)->withUnencryptedCookie(Lang::COOKIE, 'ro')->get(route('account.profile'))
            ->assertOk()->assertSee('Profilul meu')->assertSee('Date personale')->assertDontSee('Личные данные');
    }

    public function test_plural_forms(): void
    {
        $this->withUnencryptedCookie(Lang::COOKIE, 'ru');
        $this->assertSame('1 очко', $this->plural(1, 'ru'));
        $this->assertSame('5 очков', $this->plural(5, 'ru'));
        $this->assertSame('1 point', $this->plural(1, 'en'));
        $this->assertSame('2 points', $this->plural(2, 'en'));
        $this->assertSame('1 punct', $this->plural(1, 'ro'));
        $this->assertSame('5 puncte', $this->plural(5, 'ro'));
        $this->assertSame('20 de puncte', $this->plural(20, 'ro'));
    }

    private function plural(int $n, string $lang): string
    {
        request()->cookies->set(Lang::COOKIE, $lang);

        return Lang::plural($n, ['очко', 'очка', 'очков'], ['point', 'points'], ['punct', 'puncte', 'de puncte']);
    }
}
