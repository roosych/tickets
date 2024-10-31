@foreach($mentions as $mention)
    <div class="d-flex justify-content-start mb-4">
        <div class="pe-3">
            <div class="fs-6 fw-semibold mb-1">
                <a href="{{ route('cabinet.tickets.show', $mention->comment->ticket_id) }}" class="fw-bold text-gray-800">
                    {{ $mention->comment->creator->name }} {{trans('common.mentions.text')}}
                    <span class="text-primary">#{{ $mention->comment->ticket_id }}</span>
                </a>
            </div>
            <div class="d-flex align-items-center mt-1 fs-6">
                <div class="text-muted me-2 fs-7">{{ $mention->created_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>
    @if (!$loop->last)
        <div class="separator mb-3"></div>
    @endif
@endforeach

@if ($mentions->isEmpty())
    <div class="d-flex justify-content-center mb-4">
        <div class="text-muted fs-6">
            {{trans('common.mentions.empty')}}
        </div>
    </div>
@endif
