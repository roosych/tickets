<div class="d-flex justify-content-{{$comment->user_id === auth()->id() ? 'end' : 'start' }} mb-10">
    <div class="d-flex flex-column align-items-{{$comment->user_id === auth()->id() ? 'end' : 'start' }}">
        <div class="d-flex align-items-center mb-2">
            <div class="symbol symbol-35px symbol-circle">
                <img alt="" src="{{$comment->creator->avatar}}">
            </div>
            <div class="ms-3">
                <p class="fs-5 fw-bold text-gray-900 me-1 mb-0">
                    {{$comment->creator->name}}
                </p>
                <div>
                    <span class="text-muted fs-7 mb-1">
                        {{$comment->created_at->isoFormat('D MMMM, HH:mm')}}
                    </span>
                </div>
            </div>
        </div>

        @if($comment->text)
            <div class="p-5 rounded bg-light-{{$comment->user_id === auth()->id() ? 'primary' : 'info'}} text-gray-900 fw-semibold mw-lg-400px text-start">
                {!! nl2br(e($comment->text)) !!}
            </div>
        @endif

        @if($comment->media->isNotEmpty())
            <div class="d-flex flex-aligns-center mt-3">
                @foreach($comment->media as $item)
                    <div class="d-flex flex-aligns-center pe-10 pe-lg-20 mb-3">
                        <img alt="{{$item->filename}}" class="w-40px me-3" src="{{ asset('assets/media/extensions/'.$item->extension.'.png') }}">
                        <div class="ms-1 fw-semibold">
                            <a href="{{ route('cabinet.tickets.media.download', $item) }}" class="fs-6 text-hover-primary fw-bold" target="_blank">
                                {{ \Illuminate\Support\Str::limit($item->filename, 15) }}
                            </a>
                            <div class="text-gray-500">
                                {{ bytes_to_mb($item->size) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
