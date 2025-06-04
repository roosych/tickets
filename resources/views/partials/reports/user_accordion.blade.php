<div class="card-header border-0">
    <div class="card-title">
        <h3 class="fw-bold mb-0">
            {{trans('common.reports.title_by_employee')}}
        </h3>
    </div>
</div>

<div id="users_tickets_accordion" class="card-body pt-0">
    @foreach($groupedTickets as $executorId => $tickets)
        @php
            $performer = $tickets->first()->performer;
        @endphp
        <div class="py-0">
            <div class="py-3 d-flex flex-stack flex-wrap">
                <div class="d-flex align-items-center collapsible collapsed rotate"
                     data-bs-toggle="collapse"
                     href="#users_tickets_accordion_{{$executorId}}"
                     role="button"
                     aria-expanded="false"
                     aria-controls="users_tickets_accordion_{{$executorId}}">
                    <div class="me-3 rotate-90">
                        <i class="ki-outline ki-right fs-3"></i>
                    </div>
                    <div class="symbol symbol-circle symbol-45px overflow-hidden me-3">
                        <div class="symbol-label">
                            <img src="{{$performer->avatar}}" alt="{{$performer->name}}" class="w-100">
                        </div>
                    </div>
                    <div class="me-3">
                        <div class="d-flex align-items-center">
                            <div class="text-gray-800 fw-bold fs-5">
                                {{$performer->name}}
                            </div>
                            <div class="badge badge-light-primary ms-5">
                                {{ count($tickets) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex my-3 ms-9">
                    <a href="{{route('cabinet.users.show', $performer)}}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3" target="_blank">
                        <span data-bs-toggle="tooltip" data-bs-trigger="hover" title="{{trans('common.reports.info')}}">
                            <i class="ki-outline ki-eye fs-3"></i>
                        </span>
                    </a>
                </div>
            </div>
            <div id="users_tickets_accordion_{{$executorId}}" class="collapse fs-6 ps-10"
                 data-bs-parent="#users_tickets_accordion"
            >
                <table class="table align-middle table-hover text-center table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-gray-500 text-center fw-bold fs-7 text-uppercase gs-0">
                        <th>{{trans('tickets.table.ticket')}}</th>
                        <th>{{trans('tickets.table.priority')}}</th>
                        <th>{{trans('tickets.table.created_at')}}</th>
                        <th>{{trans('common.reports.assigned_at')}}</th>
                        <th>{{trans('common.reports.acceptance_at')}}</th>
                        <th>{{trans('common.reports.execution_at')}}</th>
                        <th>{{trans('common.reports.closed_at')}}</th>
                        <th>{{trans('tickets.table.tags')}}</th>
                        <th class="text-end"></th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">

                    @foreach($tickets as $ticket)
                        @php
                            $statuses = $ticket->getLastStatusChangeTimes($performer->id);

                            $lastStatusInProgressTime = getStatusChangeTime($statuses, \App\Enums\TicketStatusEnum::IN_PROGRESS);
                            $lastStatusOpenedTime = getStatusChangeTime($statuses, \App\Enums\TicketStatusEnum::OPENED);
                            $lastStatusDoneTime = getStatusChangeTime($statuses, \App\Enums\TicketStatusEnum::DONE);
                            $lastStatusCompletedTime = getStatusChangeTime($statuses, \App\Enums\TicketStatusEnum::COMPLETED);

                            // Используем последнее время открытия или время создания тикета
                            $startTime = $lastStatusOpenedTime ?? \Carbon\Carbon::parse($ticket->created_at);
                        @endphp

                        <tr class="position_row_{{$ticket->id}}">
                            <td class="ps-3">
                                <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->id}}</a>
                            </td>
                            <td>
                                <span class="badge badge-light-{{$ticket->priority->class}} fw-bold fs-7">
                                    {{$ticket->priority->getNameByLocale()}}
                                </span>
                            </td>
                            <td>
                                {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMM, HH:mm')}}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($startTime)->isoFormat('D MMM, HH:mm') }}
                            </td>
                            <td>
                                @if ($lastStatusInProgressTime)
                                    @php
                                        $difference = $startTime->diff(\Carbon\Carbon::parse($lastStatusInProgressTime));
                                        $totalMinutes = ($difference->h * 60) + $difference->i;
                                    @endphp

                                    <p class="fw-bold mb-0 {{ $totalMinutes > $ticket->priority->minutes ? 'text-danger' : 'text-success' }}">
                                        {{ formatTimeDifference($difference) }}
                                    </p>
                                @else
                                    <span class="badge badge-light-{{$ticket->status->color()}} fw-bold fs-7">
                                        {{$ticket->status->label()}}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($lastStatusDoneTime)
                                    {{ formatTimeDifference(calculateTimeDifference($lastStatusDoneTime, $lastStatusInProgressTime)) }}
                                @else
                                    @if($ticket->status->is(\App\Enums\TicketStatusEnum::IN_PROGRESS))
                                        <span class="badge badge-light-{{$ticket->status->color()}} fw-bold fs-7">
                                            {{$ticket->status->label()}}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if ($lastStatusCompletedTime)
                                    <p class="fw-bold mb-0">
                                        {{ formatTimeDifference(calculateTimeDifference($startTime, $lastStatusCompletedTime)) }}
                                    </p>
                                @else
                                    @if($ticket->status->is(\App\Enums\TicketStatusEnum::DONE))
                                        <span class="badge badge-light-{{$ticket->status->color()}} fw-bold fs-7">
                                            {{$ticket->status->label()}}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @php
                                    $tagsHtml = '';
                                       if ($ticket->tags->isNotEmpty()) {
                                        $tagsHtml = '<div>';
                                        foreach ($ticket->tags as $tag) {
                                            $tagsHtml .= "<p class='fs-6 fw-bold mb-0'>{$tag->text}</p>";
                                        }
                                        $tagsHtml .= '</div>';
                                    }
                                @endphp
                                <span class="badge badge-light-secondary badge-circle cursor-help fw-bold fs-7"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-original-title="{{ $tagsHtml }}"
                                >{{count($ticket->tags)}}</span>
                            </td>
                            <td class="text-end pe-2">
                                <div class="my-3 ms-9">
                                    <a href="{{route('cabinet.tickets.show', $ticket)}}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px" target="_blank">
                                        <span data-bs-toggle="tooltip"
                                              data-bs-trigger="hover"
                                              aria-label="{{trans('tickets.table.more')}}"
                                              data-bs-original-title="{{trans('tickets.table.more')}}">
                                            <i class="ki-outline ki-black-right fs-2 text-gray-500"></i>
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if (!$loop->last)
            <div class="separator separator-dashed"></div>
        @endif

    @endforeach
</div>
