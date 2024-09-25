<div class="notice d-flex bg-light-{{ $color }} rounded border-{{ $color }} border border-dashed mb-9 p-6">
    <i class="ki-outline {{ $icon }} fs-2tx text-{{ $color }} me-4"></i>
    <div class="d-flex flex-stack flex-grow-1">
        <div class="fw-semibold">
            <h4 class="text-gray-900 fw-bold mb-0">
                {{ $title }}
            </h4>
            @if($message)
                <div class="fs-6 text-gray-700">
                    {{ $message }}
                </div>
            @endif
        </div>
    </div>
</div>
