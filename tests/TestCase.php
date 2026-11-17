<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // режим игры хранится в файле: каждый тест начинает с выключенной игры
        @unlink(\App\Services\GameService::modeFile());
        // AI-матчи, AI-поиск и база знаний выключены в config/features.php; тесты самих разделов включают их (см. CabinetFeaturesTest)
        // тестовый клиент шлёт Accept-Language: en-us; тесты кабинета по умолчанию проверяют русский текст
        $this->withUnencryptedCookie(\App\Support\Lang::COOKIE, 'ru');
        config(['features.cabinet_matches' => true, 'features.cabinet_search' => true, 'features.cabinet_knowledge' => true]);
    }

    //
}
