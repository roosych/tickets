<div class="card-header border-0">
    <div class="card-title">
        <h3 class="fw-bold mb-0">
            {{trans('common.reports.title_by_priorities')}}
        </h3>
    </div>
</div>

<div id="priority_tickets_accordion" class="card-body pt-0">
    @foreach ($groupedTickets as $priorityId => $tickets)
        <div class="py-0">
            <div class="py-5 d-flex flex-stack flex-wrap">
                <div class="d-flex align-items-center collapsible collapsed rotate"
                     data-bs-toggle="collapse"
                     href="#priority_tickets_accordion_{{$priorityId}}"
                     role="button"
                     aria-expanded="false"
                     aria-controls="priority_tickets_accordion_{{$priorityId}}">
                    <div class="me-3 rotate-90">
                        <i class="ki-outline ki-right fs-3"></i>
                    </div>

                    <div class="me-3">
                        <div class="d-flex align-items-center">
                            <div class="text-gray-800 fw-bold fs-5">
                                {{ $tickets->first()->priority->getNameByLocale() ?? 'Неизвестный приоритет' }}
                            </div>
                            <div class="badge badge-light-primary ms-5">
                                {{ $tickets->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="priority_tickets_accordion_{{$priorityId}}" class="collapse fs-6 ps-10"
                 data-bs-parent="#priority_tickets_accordion"
            >
                <table class="table align-middle table-hover text-center table-row-dashed fs-6 gy-5">
                    <thead>
                    <tr class="text-start text-gray-500 text-center fw-bold fs-7 text-uppercase gs-0">
                        <th class="">{{trans('tickets.table.ticket')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.creator')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.created_at')}}</th>
                        <th class="min-w-125px">{{trans('tickets.table.status')}}</th>
                        <th>{{trans('tickets.table.ticket')}}</th>
                        <th>{{trans('tickets.table.tags')}}</th>
                        <th class="text-end min-w-100px"></th>
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @foreach ($tickets as $ticket)
                        <tr class="position_row_{{$ticket->id}}">
                            <td class="ps-3">
                                <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->id}}</a>
                            </td>
                            <td class="d-flex align-items-center border-bottom-0">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <a href="{{route('cabinet.users.show', $ticket->creator)}}" target="_blank">
                                        @if($ticket->creator->avatar)
                                            <div class="symbol-label">
                                                <img src="{{$ticket->creator->avatar}}" alt="{{$ticket->creator->name}}" class="w-100" />
                                            </div>
                                        @else
                                            <div class="symbol-label fs-3 bg-light-dark text-dark">
                                                {{get_initials($ticket->creator->name)}}
                                            </div>
                                        @endif
                                    </a>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{route('cabinet.users.show', $ticket->creator)}}" target="_blank" class="text-gray-800 text-hover-primary mb-1">
                                        {{$ticket->creator->name}}
                                    </a>
                                    <span>{{$ticket->creator->department}}</span>
                                </div>
                            </td>
                            <td>
                                {{\Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMM, HH:mm')}}
                            </td>
                            <td>
                                <span class="badge badge-light-{{$ticket->status->color()}} fw-bold fs-7">
                                    {{$ticket->status->label()}}
                                </span>
                            </td>
                            <td>
                                @if($ticket->parent)
                                    <a href="{{route('cabinet.tickets.show', $ticket->parent->id)}}" class="text-gray-800 text-hover-primary fw-bold" target="_blank">#{{$ticket->parent->id}}</a>
                                @else
                                    {{trans('tickets.table.no_parent')}}
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
