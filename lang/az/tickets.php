<?php

declare(strict_types=1);

return [
    'statuses' => [
        'opened' => 'Açıqdır',
        'in_progress' => 'İcra olunur',
        'done' => 'İcra olundu',
        'ticket_done' => 'Tiket bağlanıb',
        'completed' => 'Bağlı',
        'canceled' => 'Ləğv olunub',
        'ticket_canceled' => 'Ləğv olunub',
    ],
    'actions' => [
        'update_status' => 'Status dəyişdirildi:',
        'commented' => 'Yeni şərh',
        'assign_user' => 'İcraçı əlavə edildi',
        'create_child' => 'Alt tiket yaradıldı',
    ],
    'table' => [
        'search' => 'Axtar...',
        'ticket' => 'Tiket',
        'create_ticket' => 'Yeni tiket',
        'creator' => 'Yaradan',
        'priority' => 'Prioritet',
        'all_priorities' => 'Bütün prioritetlər',
        'created_at' => 'Yaradılıb',
        'status' => 'Status',
        'all_statuses' => 'Bütün statuslar',
        'performer' => 'İcraçı',
        'tags' => 'taqlar',
        'actions' => 'Əməlliyatlar',
        'more' => 'Ətraflı',
        'empty' => 'Tiket yoxdur',
        'no_parent' => 'əsas tiket',
    ],
    'create_modal' => [
        'title' => 'Yeni tiket',
        'description' => 'Təsvir',
        'description_hint' => 'Tiket nə qədər ətraflı təsvir edilsə, bir o qədər tez həll olunur :)',
        'description_placeholder' => 'Tiketin təsviri',
        'department' => 'Şöbə',
        'performer' => 'Əməkdaş',
        'client' => 'Sifarişçi',
        'tags' => 'taqlər',
        'priority' => 'Prioritet',
        'select_from_list' => 'Siyahıdan seçin...',
        'attachments' => 'Əlavələr',
        'attachments_hint' => 'Faylları yerləşdirin və ya yükləmək üçün klikləyin.',
        'format_error' => 'Format dəstəklənmir',
        'size_limit' => 'Fayl çox böyükdür',
    ],
    'cancel_modal' => [
        'title' => 'Ləğv etmək',
        'comment_label' => 'Şərh',
        'comment_placeholder' => 'Mətni daxil edin',
    ],
    'buttons' => [
        'close' => 'Bağla',
        'create' => 'Əlavə et',
        'send' => 'Göndər',
        'start_ticket' => 'İcra etmək',
        'close_ticket' => 'Bağlamaq',
        'cancel_ticket' => 'Ləğv etmək',
        'done_ticket' => 'Bağlamaq',
    ],
    'sent' => [
        'opened' => 'Açıq olan',
        'in_progress' => 'İcra olunan',
        'done' => 'Bağlanan',
        'performer_empty' => 'icraçı yoxdur',
    ],
    'parent_ticket_cancelled' => 'Əsas tiket ləğv olundu',
    'tags_text1' => 'Tiketə taqlər əlavə edə bilərsiniz, lakin departamentinizə aid olan taq yoxdur.',
    'tags_text2' => 'Yaratmaq üçün linkə',
    'tags_text_link' => 'keçid edin',
    'responsible' => 'İcraçı',
    'children_title' => 'Alt tiketlər',
    'children_title_empty' => 'Alt tiketlər yoxdur',
    'activity' => 'Tarixçə və şərhlər',
    'send_comment' => 'Şərh yaz',
    'add_file' => 'Fayl əlavə et',
    'my_opened_tickets' => 'Açıq tiketlərim',
    'mention_hint' => 'əməkdaşı qeyd etmək üçün "@" ilə başlayın',
];
