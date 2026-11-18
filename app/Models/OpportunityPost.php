<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\OpportunityFeed;
use Illuminate\Database\Eloquent\Model;

/** Одна «Возможность» (поездка или курс за рубежом). Добавляется и правится в админке, показывается на сайте и в кабинете. */
class OpportunityPost extends Model
{
    protected $fillable = [
        'slug', 'name', 'title', 'flag', 'country', 'start_date', 'end_date', 'deadline', 'posted_on',
        'text', 'detail', 'image', 'source_url', 'published',
    ];

    protected $casts = [
        'title'      => 'array',
        'country'    => 'array',
        'text'       => 'array',
        'detail'     => 'array',
        'start_date' => 'date',
        'end_date'   => 'date',
        'deadline'   => 'date',
        'posted_on'  => 'date',
        'published'  => 'boolean',
    ];

    protected static function booted(): void
    {
        // публичные страницы читают ленту из кэша: после любой правки он сбрасывается
        static::saved(fn () => OpportunityFeed::forget());
        static::deleted(fn () => OpportunityFeed::forget());
    }

    /** Языки, на которых показывается сайт. */
    public const LANGS = ['ru' => 'Русский', 'en' => 'English', 'ro' => 'Română'];

    /**
     * Пустые языки заполняются английским текстом, чтобы на сайте и в кабинете никогда не было пустого места.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function fillMissingLanguages(array $data): array
    {
        foreach (['country', 'text'] as $key) {
            foreach (['ru', 'ro'] as $l) {
                if (blank($data[$key][$l] ?? null)) {
                    $data[$key][$l] = $data[$key]['en'] ?? '';
                }
            }
        }

        foreach (['ru', 'ro'] as $l) {
            $own = array_filter((array) ($data['detail'][$l] ?? []), fn ($v) => filled($v));
            if ($own === []) {
                $data['detail'][$l] = $data['detail']['en'] ?? [];
            }
        }

        return $data;
    }
}
