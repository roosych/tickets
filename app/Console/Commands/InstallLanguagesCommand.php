<?php

namespace App\Console\Commands;

use App\Models\Language;
use Illuminate\Console\Command;

class InstallLanguagesCommand extends Command
{
    protected $signature = 'languages:install';

    protected $description = 'Install app languages';

    public function handle()
    {
        $this->info('Installing languages...');
        $this->installLanguages();
        $this->info('Languages installed!');
    }

    public function installLanguages()
    {
        //если языки существуют то повторно не создавать а то метод upsert перетрёт все их настройки
        if (Language::query()->exists()) {
            return;
        }

        $templates = [
            ['id' => 'az', 'name' => 'Azərbaycanca', 'active' => true, 'default' => true, 'fallback' => true],
            ['id' => 'ru', 'name' => 'Русский', 'active' => true, 'default' => false, 'fallback' => false],
            ['id' => 'en', 'name' => 'English', 'active' => false, 'default' => false, 'fallback' => false],
        ];

        //Метод Upsert для добавления/обновления нескольких записей разом Новая функция в Laravel,
        // которая позволит вам удобно обновить или синхронизировать большой объем данных одним запросом.
        // Метод Upsert делает insert записей, которых нет в базе и update для тех, что есть.
        Language::query()->upsert($templates, 'id');
    }
}
