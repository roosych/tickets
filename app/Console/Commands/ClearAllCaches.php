<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAllCaches extends Command
{
    // Название и описание команды
    protected $signature = 'cache:clear-all';
    protected $description = 'Clear all application caches (config, route, view, cache)';

    // Логика выполнения команды
    public function handle()
    {
        // Очистка всех типов кешей
        $this->call('cache:clear');
        $this->info('Application cache cleared.');

        $this->call('route:clear');
        $this->info('Route cache cleared.');

        $this->call('config:clear');
        $this->info('Configuration cache cleared.');

        $this->call('view:clear');
        $this->info('View cache cleared.');

        $this->info('All caches have been cleared successfully!');
    }
}
