<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/** Настройки из админки. Секреты (ключи API) хранятся зашифрованными ключом приложения (APP_KEY). */
class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function secret(string $key): ?string
    {
        $raw = static::find($key)?->value;
        if (blank($raw)) {
            return null;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null;   // сменился APP_KEY или значение повреждено: считаем, что ключ не задан
        }
    }

    public static function putSecret(string $key, ?string $value): void
    {
        if (blank($value)) {
            static::whereKey($key)->delete();

            return;
        }

        static::updateOrCreate(['key' => $key], ['value' => Crypt::encryptString(trim($value))]);
    }
}
