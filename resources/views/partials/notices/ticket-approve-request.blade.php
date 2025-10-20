<div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
    <i class="ki-outline ki-send fs-2tx text-primary me-4"></i>

    <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
        <div class="mb-3 mb-md-0 fw-semibold">
            <h4 class="text-gray-900 fw-bold">
                Отправка на одобрение
            </h4>

            <div class="fs-6 text-gray-700 pe-7">
                Если нужно согласование, нажмите кнопку и выберите подходящий пункт.
            </div>
        </div>

        <button class="btn btn-sm btn-primary px-6 align-self-center text-nowrap"
                data-bs-toggle="modal"
                data-bs-target="#approveRequestModal"
                data-id="{{ $ticket->id }}">
            Отправить
        </button>
    </div>
</div>
