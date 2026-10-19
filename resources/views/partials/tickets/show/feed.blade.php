        <div class="col-xxl-4">
            <div class="card" id="kt_chat_messenger">
                <div class="card-header" id="kt_chat_messenger_header">
                    <div class="card-title">
                        {{trans('tickets.activity')}}
                    </div>
                    <div class="card-toolbar gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-light btn-active-light-primary active" data-feed-filter="all">{{trans('tickets.feed.all')}}</button>
                            <button type="button" class="btn btn-light btn-active-light-primary" data-feed-filter="comment">{{trans('tickets.feed.comments')}}</button>
                            <button type="button" class="btn btn-light btn-active-light-primary" data-feed-filter="status">{{trans('tickets.feed.statuses')}}</button>
                        </div>
                    </div>
                </div>

                <div class="card-body" id="kt_chat_messenger_body">
                    @if($activities->contains(fn ($a) => $a instanceof \App\Models\TicketHistory && $a->action->is(\App\Enums\TicketActionEnum::VIEWED)))
                        <label class="form-check form-check-custom form-check-solid form-check-sm mb-4">
                            <input class="form-check-input" type="checkbox" id="feed_show_views">
                            <span class="form-check-label text-muted fs-7">{{trans('tickets.feed.show_views')}}</span>
                        </label>
                    @endif
                    <div class="scroll-y me-n5 pe-5 h-300px h-lg-auto feed-hide-views" id="chat-messages"
                         data-kt-element="messages"
                         data-kt-scroll="true"
                         data-kt-scroll-activate="{default: false, lg: true}"
                         data-kt-scroll-max-height="300px"
                         data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_app_toolbar, #kt_toolbar, #kt_footer, #kt_app_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                         data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_messenger_body"
                         data-kt-scroll-offset="5px">
                        @forelse($activities as $activity)
                            @php
                                $feedType = match (true) {
                                    $activity instanceof \App\Models\Comment => 'comment',
                                    $activity instanceof \App\Models\ApprovalRequestHistory => 'approval',
                                    $activity->action->is(\App\Enums\TicketActionEnum::VIEWED) => 'view',
                                    default => 'status',
                                };
                            @endphp
                            <div class="feed-item" data-type="{{$feedType}}">
                            @if($activity instanceof \App\Models\Comment)
                                <x-ticket-comment :comment="$activity"></x-ticket-comment>
                            @elseif($activity instanceof \App\Models\TicketHistory)
                                <div class="d-flex align-items-center justify-content-start mt-1 fs-6 mb-5">

                                    @if($activity->action->is(\App\Enums\TicketActionEnum::ASSIGN_USER))
                                        <div class="text-muted me-2 fs-7">
                                            {{ $activity->action->label() }}
                                            <strong class="text-gray-800 me-1">{{ $activity->assignUser->name }}</strong>
                                            {{ $activity->created_at->isoFormat('D MMM YYYY, HH:mm') }}
                                        </div>
                                    @elseif($activity->action->is(\App\Enums\TicketActionEnum::VIEWED))
                                        <div class="text-muted me-2 fs-7">
                                            {{trans('tickets.actions.viewed')}}
                                            {{ $activity->created_at->isoFormat('D MMM YYYY, HH:mm') }}
                                        </div>

                                    @elseif($activity->action->is(\App\Enums\TicketActionEnum::UPDATE_DEADLINE))
                                        <div class="text-muted me-2 fs-7">
                                            <strong class="text-gray-800">
                                                {{trans('tickets.actions.update_deadline')}}
                                            </strong>
                                            {{ $activity->created_at->isoFormat('D MMM YYYY, HH:mm') }}
                                        </div>
                                    @else
                                        <div class="text-muted me-2 fs-7">
                                            {{ $activity->action->label() }}
                                            <span class="badge badge-light-{{$activity->status->color()}}">
                                                {{ $activity->status->label() }}
                                            </span>
                                            {{ $activity->created_at->isoFormat('D MMM YYYY, HH:mm') }}
                                        </div>
                                    @endif

                                    <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                         data-bs-boundary="window"
                                         data-bs-placement="top"
                                         aria-label="{{ $activity->user->name }}"
                                         data-bs-original-title="{{ $activity->user->name }}">
                                        @if($activity->user->avatar)
                                            <img src="{{ $activity->user->avatar }}" alt="{{ $activity->user->name }}">
                                        @else
                                            <div class="symbol-label fs-7 bg-light-dark text-gray-800">
                                                {{ get_initials($activity->user->name) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($activity->action->is(\App\Enums\TicketActionEnum::UPDATE_DEADLINE))
                                    <div class="p-5 mb-10 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-start">
                                        {{$activity->comment}}
                                    </div>
                                @endif

                                @if($activity->status === \App\Enums\TicketStatusEnum::DONE)
                                    <div class="p-5 mb-10 rounded bg-light-primary text-gray-900 fw-semibold mw-lg-400px text-start">
                                        {{$ticket->getDoneTicketComment()}}
                                    </div>
                                @endif

                                @if($activity->status === \App\Enums\TicketStatusEnum::CANCELED)
                                    <div class="p-5 mb-10 rounded bg-light-danger text-gray-900 fw-semibold mw-lg-400px text-start">
                                        {{$ticket->getCanceledTicketComment()}}
                                    </div>
                                @endif

                                @if($activity->status === \App\Enums\TicketStatusEnum::COMPLETED && $ticket->rating)
                                    @php
                                        $rating = $ticket->rating->rating ?? 0;
                                    @endphp

                                    <div class="rating justify-content-start">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <div class="rating-label {{ $i <= $rating ? 'checked' : '' }}">
                                                <i class="ki-duotone ki-star fs-2"></i>
                                            </div>
                                        @endfor
                                    </div>

                                    <div class="p-5 mt-3 rounded bg-light-success text-gray-900 fw-semibold mw-lg-400px text-start">
                                        {{$ticket->rating->comment}}
                                    </div>
                                @endif
                            @elseif($activity instanceof \App\Models\ApprovalRequestHistory)
                                @php
                                    $history = $activity;
                                    $requester = $history->user;
                                    $approver = $approvalRequestsById[$history->approval_request_id]->approver ?? null;
                                @endphp
                                @if($requester && $approver)
                                    <div class="text-muted me-2 mb-4 fs-7">

                                        @if($history->status->is(\App\Enums\TicketApprovalRequestStatusEnum::APPROVED))
                                            <div>
                                                <span class="fw-bold text-gray-800">
                                                    {{ $approver->name }}:
                                                </span>
                                                <span class="badge badge-light-{{$history->status->color()}} fw-bold fs-7">
                                                    {{$history->status->label()}}
                                                </span>
                                                <span class="me-1">{{ $history->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                                            </div>
                                            @elseif($history->status->is(\App\Enums\TicketApprovalRequestStatusEnum::DENIED))
                                                <div>
                                                    <span class="fw-bold text-gray-800">
                                                        {{ $approver->name }}:
                                                    </span>
                                                    <span class="badge badge-light-{{$history->status->color()}} fw-bold fs-7">
                                                        {{$history->status->label()}}
                                                    </span>
                                                    <span class="me-1">{{ $history->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>
                                                </div>
                                            @else
                                            <div class="text-muted me-2 fs-7">
                                                Запрос на одобрение,
                                                <span>{{ $history->created_at->isoFormat('D MMM YYYY, HH:mm') }}</span>

                                                <div class="fw-bold text-gray-800 me-2 fs-7 mt-1">
                                                    {{ $requester->name }}
                                                    <i class="ki-duotone ki-black-right fs-5 mx-2"></i>
                                                    {{ $approver->name }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                            </div>
                        @empty
                            <div class="text-center empty_activity">
                                <img src="{{asset('assets/media/misc/13.png')}}" class="w-200px" alt="">
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($canComment)
                    <form method="POST" id="send_comment_form">
                        @csrf
                        <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                            <input type="hidden" name="temp_folder" id="comment_temp_folder">
                            <textarea id="tribute" class="form-control form-control-flush mb-3 bg-light rounded" rows="3" name="text" placeholder="{{trans('tickets.send_comment')}}" style="width: 100%;"></textarea>
                            <div class="fs-7 text-muted">
                                 {{trans('tickets.mention_hint')}}
                            </div>

                            <div id="comment_filepond">
                                <input type="file" class="comment_attach" name="media" multiple />
                            </div>

                            <div class="d-flex flex-stack">
                                <div class="d-flex align-items-center me-2">
                                    <button class="btn btn-sm btn-icon btn-active-light-primary me-1 show-filepond" type="button">
                                        <i class="ki-outline ki-paper-clip fs-3"></i>
                                    </button>
                                </div>
                                <button id="send_comment_btn"
                                        class="btn btn-primary"
                                        type="submit">
                                    <i class="ki-outline ki-send fs-2"></i>
                                    {{trans('tickets.buttons.send')}}
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
