<div class="d-flex flex-column flex-center text-center py-10">
    <i class="ki-outline ki-check-circle fs-3tx text-success mb-4"></i>
    <h4 class="text-gray-900 fw-bold mb-2">{{ $title }}</h4>
    <div class="text-gray-600 fs-6 mb-5">{{ $text }}</div>
    @isset($btnHref)
        <a href="{{ $btnHref }}" class="btn btn-sm btn-light-primary">{{ $btnLabel }}</a>
    @endisset
    @isset($btnModal)
        <a href="#" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="{{ $btnModal }}">
            <i class="ki-outline ki-plus-square fs-3"></i>{{ $btnLabel }}
        </a>
    @endisset
</div>
