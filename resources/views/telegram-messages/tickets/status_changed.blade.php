🔄 <b>Изменение статуса тикета #{{ $ticket->id }}</b>

изменил: <b>{{ $initiator?->name ?? 'Система' }}</b>
новый статус: <b>{{ trans($ticket->status->label()) ?? 'Не указан' }}</b>

👉 <a href="{{ config('app.url') }}/cabinet/tickets/{{ $ticket->id }}">Перейти к тикету</a>
