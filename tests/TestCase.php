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
    }

    //
}
