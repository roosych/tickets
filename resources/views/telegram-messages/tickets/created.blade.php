@if($ticket->parent)
🔔 <b>Новый подтикет для #{{ $ticket->parent->id }}</b>
@else
🔔 <b>Новый тикет #{{ $ticket->id }}</b>
@endif

автор: <b>{{ $ticket->creator?->name ?? 'Неизвестный пользователь' }}</b>
исполнитель: <b>{{ $ticket->performer?->name ?? '🤷‍' }}</b>

@if($ticket->text)
    "{{ $ticket->text }}"
@endif

@if($ticket->parent)
👉 <a href="{{ config('app.url') }}/cabinet/tickets/{{ $ticket->parent->id }}">Родительский тикет</a>
@endif
👉 <a href="{{ config('app.url') }}/cabinet/tickets/{{ $ticket->id }}">Перейти к тикету</a>
