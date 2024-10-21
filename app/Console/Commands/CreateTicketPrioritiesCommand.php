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
                'name' => json_encode(['en' => 'Urgent', 'ru' => 'Срочно', 'az' => 'Təcili']),
                'class' => 'danger',
                'minutes' => 5
            ],
            [
                'name' => json_encode(['en' => 'High', 'ru' => 'Высокий', 'az' => 'Yüksək']),
                'class' => 'warning',
                'minutes' => 20
            ],
            [
                'name' => json_encode(['en' => 'Normal', 'ru' => 'Нормальный', 'az' => 'Normal']),
                'class' => 'primary',
                'minutes' => 60
            ],
            [
                'name' => json_encode(['en' => 'Low', 'ru' => 'Низкий', 'az' => 'Aşağı']),
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
