<?php

namespace App\Console\Commands;

use App\Models\Priorities;
use Illuminate\Console\Command;

class CreateTicketPrioritiesCommand extends Command
{
    protected $signature = 'priorities:create';
    protected $description = 'Add priorities for tickets';

    public function handle()
    {
        $this->info('Creating priorities...');
        $this->createPriorities();
        $this->info('Priorities installed!');
    }

    public function createPriorities()
    {
        //если приоритеты существуют то повторно не создавать а то метод upsert перетрёт все их настройки
        if (Priorities::query()->exists()) {
            return;
        }

        $priorities = [
            [
                'name' => json_encode(['en' => 'Urgent', 'ru' => 'Urgent', 'az' => 'Urgent']),
                'class' => 'danger',
                'minutes' => 10
            ],
            [
                'name' => json_encode(['en' => 'High', 'ru' => 'High', 'az' => 'High']),
                'class' => 'warning',
                'minutes' => 30
            ],
            [
                'name' => json_encode(['en' => 'Normal', 'ru' => 'Normal', 'az' => 'Normal']),
                'class' => 'primary',
                'minutes' => 60
            ],
            [
                'name' => json_encode(['en' => 'Low', 'ru' => 'Low', 'az' => 'Low']),
                'class' => 'secondary',
                'minutes' => 100
            ],
        ];

        //Метод Upsert для добавления/обновления нескольких записей разом Новая функция в Laravel,
        // которая позволит вам удобно обновить или синхронизировать большой объем данных одним запросом.
        // Метод Upsert делает insert записей, которых нет в базе и update для тех, что есть.
        Priorities::query()->upsert($priorities, 'id');
    }
}
