<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Telegram\Conversations\RegistrationConversation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RegistrationNameTest extends TestCase
{
    #[DataProvider('names')]
    public function test_full_name_check(string $text, bool $ok): void
    {
        $this->assertSame($ok, RegistrationConversation::looksLikeFullName($text), $text);
    }

    public static function names(): array
    {
        return [
            'команда бота'  => ['/start', false],
            'пусто'         => ['', false],
            'одно слово'    => ['Анна', false],
            'цифры'         => ['Анна 123', false],
            'ссылка'        => ['https://example.com сайт', false],
            'почта'         => ['anna@example.com', false],
            'нормальное'    => ['Анна Лунгу', true],
            'румынское'     => ['Ștefan Țurcanu', true],
            'три слова'     => ['Иван Иванович Петров', true],
            'латиница'      => ['Pavel Lesnichenko', true],
        ];
    }
}
