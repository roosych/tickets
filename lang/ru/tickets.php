<?php

declare(strict_types=1);

return [
    'statuses' => [
        'opened' => 'Открытый',
        'in_progress' => 'В процессе',
        'done' => 'Выполнен',
        'ticket_done' => 'Тикет закрыт',
        'completed' => 'Закрыт',
        'canceled' => 'Отменён',
        'ticket_canceled' => 'Отменён',
    ],
    'actions' => [
        'update_status' => 'Тикет',
        'commented' => 'Новый комментарий',
        'assign_user' => 'Тикет перешел к',
        'create_child' => 'Создан подтикет',
    ],
    'table' => [
        'search' => 'Поиск...',
        'ticket' => 'Тикет',
        'main_ticket' => 'Основной тикет',
        'create_ticket' => 'Создать тикет',
        'creator' => 'Автор',
        'priority' => 'Приоритет',
        'all_priorities' => 'Все приоритеты',
        'created_at' => 'Создан',
        'status' => 'Статус',
        'all_statuses' => 'Все статусы',
        'performer' => 'Исполнитель',
        'dept_users' => 'Сотрудники отдела',
        'tags' => 'Теги',
        'actions' => 'Действия',
        'more' => 'Подробнее',
        'empty' => 'Нет тикетов',
        'no_parent' => 'не вложен',
    ],
    'create_modal' => [
        'title' => 'Новый тикет',
        'description' => 'Описание',
        'description_hint' => 'Чем более подробно описан тикет, тем быстрее он решается :)',
        'description_placeholder' => 'Опишите ситуацию, задачу или проблему',
        'department' => 'Отдел',
        'performer' => 'Сотрудник',
        'client' => 'Заказчик',
        'tags' => 'Теги',
        'priority' => 'Приоритет',
        'select_from_list' => 'Выберите из списка...',
        'attachments' => 'Вложения',
        'attachments_hint' => 'Перетащите файлы сюда или нажмите, чтобы загрузить.',
        'format_error' => 'Не поддерживаемый тип файла',
        'size_limit' => 'Файл слишком большой',
    ],
    'cancel_modal' => [
        'title' => 'Отменить тикет',
        'comment_label' => 'Комментарий',
        'comment_placeholder' => 'Введите текст',
    ],
    'buttons' => [
        'close' => 'Закрыть',
        'create' => 'Создать',
        'send' => 'Отправить',
        'start_ticket' => 'Начать выполнение',
        'close_ticket' => 'Закрыть тикет',
        'cancel_ticket' => 'Отменить тикет',
        'done_ticket' => 'Закончить выполнение',
    ],
    'sent' => [
        'opened' => 'Открытые',
        'in_progress' => 'В процессе',
        'done' => 'Выполненные',
        'performer_empty' => 'нет исполнителя',
    ],
    'parent_ticket_cancelled' => 'Родительский тикет отменен',
    'tags_text1' => 'Вы можете добавлять теги к тикету, но у Вашего отдела еще нет тегов.',
    'tags_text2' => 'Для создания перейдите по',
    'tags_text_link' => 'ссылке',
    'responsible' => 'Ответственный',
    'children_title' => 'Вложенные тикеты',
    'children_title_empty' => 'Нет вложенных тикетов',
    'activity' => 'Активность',
    'send_comment' => 'Оставить комментарий',
    'add_file' => 'Прикрепить файл',
    'my_opened_tickets' => 'Мои открытые тикеты',
    'done_tickets' => 'Выполненные тикеты',
    'mention_hint' => 'начните с "@" для упоминания сотрудника',
    'my_open' => 'Мои открытые',
    'wait_close' => 'Ожидают закрытия',
    'empty_out' => 'Вы не создавали тикетов',
    'empty_wait_close' => 'Нет тикетов ожидающих закрытия',
    'has_children' => 'Есть подтикеты',
    'validations' => [
        'text_required' => 'Заполните описание тикета',
        'text_max' => 'Описание не может содержать более 10000 символов',
        'priority_required' => 'Выберите приоритет',
        'priority_exists' => 'Приоритет не существует',
        'department_required' => 'Выберите отдел',
        'department_exists' => 'Отдел не существует',
        'files_mimes' => 'Допустимые форматы файлов: jpeg, png, pdf, doc, docx, xls, xlsx',
        'files_max' => 'Максимальный размер файла не должен превышать 4 МБ.',

        'comment' => [
            'text_required' => 'Введите комментарий',
            'text_string' => 'Введите текст',
            'text_max' => 'Слишком длинный комментарий',
        ],

    ],
];
