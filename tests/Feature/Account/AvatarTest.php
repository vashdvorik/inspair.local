<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\BotUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Аватарка участника: читается с диска напрямую, а при отсутствии файла подтягивается из Telegram. */
class AvatarTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attrs = []): BotUser
    {
        static $n = 890000;
        $n++;

        return BotUser::create($attrs + ['telegram_id' => $n, 'telegram_username' => 'user'.$n, 'full_name' => 'Test '.$n, 'status' => BotUser::STATUS_APPROVED]);
    }

    private function sess(BotUser $u): array
    {
        return ['account_telegram_id' => $u->telegram_id, '_account_expires' => now()->addDay()->timestamp];
    }

    private function fakeTelegram(bool $hasPhoto = true): void
    {
        config(['nutgram.token' => 'TEST:TOKEN']);
        Http::fake([
            'api.telegram.org/botTEST:TOKEN/getUserProfilePhotos*' => Http::response(['ok' => true, 'result' => ['total_count' => $hasPhoto ? 1 : 0, 'photos' => $hasPhoto ? [[['file_id' => 'small'], ['file_id' => 'big']]] : []]]),
            'api.telegram.org/botTEST:TOKEN/getFile*' => Http::response(['ok' => true, 'result' => ['file_path' => 'photos/file_1.jpg']]),
            'api.telegram.org/file/botTEST:TOKEN/photos/file_1.jpg' => Http::response('JPEGDATA'),
        ]);
    }

    public function test_missing_file_is_fetched_from_telegram_and_served(): void
    {
        Storage::fake('public');
        Cache::flush();
        $this->fakeTelegram();
        $u = $this->user(['avatar_path' => 'avatars/gone.jpg']);

        $this->withSession($this->sess($u))->get(route('account.avatar', $u))->assertOk();

        Storage::disk('public')->assertExists("avatars/{$u->telegram_id}.jpg");
        $this->assertSame("avatars/{$u->telegram_id}.jpg", $u->fresh()->avatar_path);
    }

    public function test_user_without_telegram_photo_gets_404(): void
    {
        Storage::fake('public');
        Cache::flush();
        $this->fakeTelegram(false);
        $u = $this->user();

        $this->withSession($this->sess($u))->get(route('account.avatar', $u))->assertNotFound();
    }

    public function test_fresh_file_is_served_without_calling_telegram(): void
    {
        Storage::fake('public');
        Cache::flush();
        Http::fake();
        $u = $this->user(['avatar_path' => 'x']);
        Storage::disk('public')->put("avatars/{$u->telegram_id}.jpg", 'JPEG');

        $this->withSession($this->sess($u))->get(route('account.avatar', $u))->assertOk();

        Http::assertNothingSent();
    }

    public function test_banned_user_is_hidden_from_others_but_sees_everyone_else(): void
    {
        Storage::fake('public');
        $owner = $this->user();
        $banned = $this->user(['status' => BotUser::STATUS_BANNED]);
        foreach ([$owner, $banned] as $u) {
            Storage::disk('public')->put("avatars/{$u->telegram_id}.jpg", 'JPEG');
        }

        // забаненный видит чужие фото, но его фото другим не показывается; своё он видит
        $this->withSession($this->sess($banned))->get(route('account.avatar', $owner))->assertOk();
        $this->withSession($this->sess($banned))->get(route('account.avatar', $banned))->assertOk();
        $this->withSession($this->sess($owner))->get(route('account.avatar', $banned))->assertNotFound();
    }

    public function test_avatar_url_is_null_without_photo(): void
    {
        $this->assertNull($this->user()->avatarUrl());
        $this->assertStringContainsString('/app/account/avatar/', $this->user(['avatar_path' => 'a'])->avatarUrl());
    }
}
