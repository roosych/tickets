<?php

declare(strict_types=1);

return [
    'tickets' => [
        'title' => 'Тикеты',
        'dept' => [
            'text' => 'Тикеты отдела',
            'hint' => 'Список тикетов отдела',
        ],
        'my' => [
            'text' => 'Мои тикеты',
            'hint' => 'Список тикетов адресованных мне',
        ],
        'sent' => [
            'text' => 'Исходящие',
            'hint' => 'Список тикетов созданных мной',
        ],
        'tags' => [
            'text' => 'Теги тикетов',
            'hint' => 'Теги для тикетов',
        ],

    ],
    'dept' => [
        'title' => 'Мой отдел',
        'users' => [
            'text' => 'Сотрудники',
            'hint' => 'Список сотрудников Вашего отдела',
        ],
        'access' => [
            'text' => 'Роли и полномочия',
            'hint' => 'Роли и полномочия сотрудников Вашего отдела',
        ],
    ],
    'stats' => [
        'title' => 'Статистика',
        'users' => [
            'text' => 'По сотрудникам',
            'hint' => 'Статистика по сотрудникам отдела',
        ],
        'dept' => [
            'text' => 'По отделам',
            'hint' => 'Статистика по отделам',
        ],
        'tags' => [
            'text' => 'По тегам',
            'hint' => 'Статистика по тегам',
        ],
    ],
];
