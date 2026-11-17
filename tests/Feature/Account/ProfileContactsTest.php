<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Дополнительные контакты в профиле: телефон, почта, Instagram. */
class ProfileContactsTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attrs = []): BotUser
    {
        static $n = 880000;
        $n++;

        return BotUser::create($attrs + ['telegram_id' => $n, 'telegram_username' => 'user'.$n, 'full_name' => 'Test '.$n, 'status' => BotUser::STATUS_APPROVED]);
    }

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    public function test_contacts_are_saved_and_instagram_link_becomes_a_username(): void
    {
        $u = $this->user();

        $this->withSession($this->sess($u))->post(route('account.profile.update'), [
            'full_name' => 'Test User',
            'contact_phone' => '+373 600 12345',
            'contact_email' => 'me@example.com',
            'contact_instagram' => 'https://www.instagram.com/my.name_1/?hl=en',
        ])->assertRedirect(route('account.profile'));

        $u->refresh();
        $this->assertSame('+373 600 12345', $u->contact_phone);
        $this->assertSame('me@example.com', $u->contact_email);
        $this->assertSame('my.name_1', $u->contact_instagram);

        // очищенные поля стираются
        $this->withSession($this->sess($u))->post(route('account.profile.update'), ['full_name' => 'Test User'])->assertRedirect();
        $this->assertNull($u->fresh()->contact_phone);
    }

    public function test_invalid_contacts_are_rejected(): void
    {
        $u = $this->user();

        $this->withSession($this->sess($u))->post(route('account.profile.update'), [
            'full_name' => 'Test User',
            'contact_phone' => 'call me maybe',
            'contact_email' => 'not-an-email',
            'contact_instagram' => 'bad nick!',
        ])->assertSessionHasErrors(['contact_phone', 'contact_email', 'contact_instagram']);
    }

    public function test_other_participants_never_see_phone_email_or_instagram(): void
    {
        $viewer = $this->user();
        $person = $this->user(['contact_phone' => '+373 600 99999', 'contact_email' => 'person@example.com', 'contact_instagram' => 'person_ig']);

        $this->withSession($this->sess($viewer))->get(route('account.people.show', $person))
            ->assertOk()->assertDontSee('+373 600 99999')->assertDontSee('person@example.com')->assertDontSee('person_ig');

        // а сам участник видит свои контакты и подпись, кто их видит
        $this->withSession($this->sess($person))->get(route('account.profile'))
            ->assertOk()->assertSee('+373 600 99999')->assertSee('person@example.com')->assertSee('@person_ig');
    }

    public function test_edit_form_has_the_contact_fields(): void
    {
        $u = $this->user(['contact_phone' => '+373 600 11111']);

        $this->withSession($this->sess($u))->get(route('account.profile.edit'))
            ->assertOk()->assertSee('name="contact_phone"', false)->assertSee('name="contact_email"', false)->assertSee('name="contact_instagram"', false)->assertSee('+373 600 11111');
    }
}
