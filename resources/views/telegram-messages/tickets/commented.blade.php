💬 <b>Новый комментарий к тикету #{{ $ticket->id }}</b>

автор: <b>{{ $comment->creator?->name ?? 'Система' }}</b>
"{{ $comment->text ?? 'Комментарий отсутствует' }}"

👉 <a href="{{ config('app.url') }}/cabinet/tickets/{{ $ticket->id }}">Перейти к тикету</a>
