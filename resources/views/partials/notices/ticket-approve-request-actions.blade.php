<div class="action_buttons notice d-flex bg-light-warning rounded border-warning border border-dashed flex-shrink-0 p-6 mt-8">
    <i class="ki-outline ki-shield-tick fs-2tx text-warning me-4"></i>
    <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">

        <div class="mb-3 mb-md-0 fw-semibold">
            <h4 class="text-gray-900 fw-bold">
                Требуется одобрение!
            </h4>
            <div class="fs-6 text-gray-700 pe-7">
                Этот тикет содержит запрос, требующий вашего решения.
            </div>
        </div>

        <a href="{{route('approval.deny', [$approvalRequest, $approvalRequest->deny_token])}}" class="btn btn-sm btn-danger px-6 align-self-center me-2 approve"
           data-id="{{$approvalRequest->id}}">
            Отклонить
        </a>
        <a href="{{route('approval.approve', [$approvalRequest, $approvalRequest->approve_token])}}" class="btn btn-sm btn-success px-6 align-self-center reject"
            data-id="{{$approvalRequest->id}}">
            Одобрить
        </a>

    </div>
</div>
