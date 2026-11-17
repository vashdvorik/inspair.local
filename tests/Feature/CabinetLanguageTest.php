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

    public function test_cabinet_pages_are_translated(): void
    {
        $u = BotUser::create(['telegram_id' => 777001, 'telegram_username' => 'lang_test', 'full_name' => 'Lang Test', 'status' => BotUser::STATUS_APPROVED]);
        $session = ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];

        $this->withSession($session)->withUnencryptedCookie(Lang::COOKIE, 'en')->get(route('account.index'))
            ->assertOk()->assertSee('Welcome to your INSPIRE Community account')->assertSee('Log out')->assertDontSee('Выйти');
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
