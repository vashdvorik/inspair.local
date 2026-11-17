<?php

return [
    // Тяжёлые пакеты (Filament, Livewire, Nutgram) подключает AppServiceProvider::registerHeavyPackages() только для своих адресов
    App\Providers\AppServiceProvider::class,
];
