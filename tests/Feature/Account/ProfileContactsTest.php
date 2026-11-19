<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Дополнительные контакты в профиле: телефон, почта, организация. */
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

    public function test_all_registration_data_can_be_edited_in_the_cabinet(): void
    {
        $u = $this->user();

        $this->withSession($this->sess($u))->post(route('account.profile.update'), [
            'full_name' => 'Test User',
            'contact_phone' => '+373 600 12345',
            'contact_email' => 'me@example.com',
            'organization' => 'Технический университет Молдовы',
            'city' => 'Кишинёв',
        ])->assertRedirect(route('account.profile'));

        $u->refresh();
        $this->assertSame('+373 600 12345', $u->contact_phone);
        $this->assertSame('me@example.com', $u->contact_email);
        $this->assertSame('Технический университет Молдовы', $u->organization);
        $this->assertSame('Кишинёв', $u->city);
        $this->assertSame('Test User', $u->full_name);

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
            'organization' => str_repeat('я', 161),
        ])->assertSessionHasErrors(['contact_phone', 'contact_email', 'organization']);
    }

    public function test_other_participants_never_see_phone_or_email(): void
    {
        $viewer = $this->user();
        $person = $this->user(['contact_phone' => '+373 600 99999', 'contact_email' => 'person@example.com']);

        $this->withSession($this->sess($viewer))->get(route('account.people.show', $person))
            ->assertOk()->assertDontSee('+373 600 99999')->assertDontSee('person@example.com');

        // а сам участник видит свои контакты и подпись, кто их видит
        $this->withSession($this->sess($person))->get(route('account.profile'))
            ->assertOk()->assertSee('+373 600 99999')->assertSee('person@example.com');
    }

    public function test_edit_form_has_the_contact_fields(): void
    {
        $u = $this->user(['contact_phone' => '+373 600 11111']);

        $this->withSession($this->sess($u))->get(route('account.profile.edit'))
            ->assertOk()->assertSee('name="contact_phone"', false)->assertSee('name="contact_email"', false)->assertSee('name="organization"', false)->assertDontSee('instagram', false)->assertSee('+373 600 11111');
    }
}
