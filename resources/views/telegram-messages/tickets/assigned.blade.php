👤 <b>Назначение на тикет #{{ $ticket->id }}</b>

назначил: <b>{{ auth()->user()?->name ?? 'Система' }}</b>
исполнитель: <b>{{ $ticket->performer?->name ?? 'Не назначен' }}</b>

👉 <a href="{{ config('app.url') }}/cabinet/tickets/{{ $ticket->id }}">Перейти к тикету</a>
