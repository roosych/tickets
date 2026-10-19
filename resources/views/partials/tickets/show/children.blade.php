            @if($ticket->department->id === auth()->user()->getDepartmentId())
                @if(!$ticket->status->is(\App\Enums\TicketStatusEnum::COMPLETED)
                    && !$ticket->status->is(\App\Enums\TicketStatusEnum::CANCELED))
                    <div class="d-flex flex-wrap flex-stack my-6">
                        <h3 class="fw-bold my-2">
                            {{$ticket->allChildren->isNotEmpty() ? trans('tickets.children_title') : trans('tickets.children_title_empty')}}
                        </h3>

                        <div class="d-flex align-items-center my-2">
                            <button class="btn btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_new_ticket">
                                <i class="ki-outline ki-plus-square fs-2"></i>
                                {{trans('tickets.buttons.create')}}
                            </button>
                        </div>
                    </div>
                @endif
                @if($ticket->allChildren->isNotEmpty())
                    @php
                        $childrenTotal = $ticket->allChildren->count();
                        $childrenDone = $ticket->allChildren->filter(fn ($c) => $c->status->is(\App\Enums\TicketStatusEnum::DONE) || $c->status->is(\App\Enums\TicketStatusEnum::COMPLETED))->count();
                        $childrenPercent = (int) round($childrenDone / $childrenTotal * 100);
                    @endphp
                    <div class="card mb-6 mb-xl-9">
                        <div class="card-body">
                            <div class="d-flex flex-stack mb-2 fw-semibold">
                                <span class="text-gray-700">{{trans('tickets.children_progress', ['done' => $childrenDone, 'total' => $childrenTotal])}}</span>
                                <span class="text-gray-800 fw-bold">{{$childrenPercent}}%</span>
                            </div>
                            <div class="h-8px bg-light rounded mb-6">
                                <div class="bg-success rounded h-8px" role="progressbar" style="width: {{$childrenPercent}}%;"
                                     aria-valuenow="{{$childrenPercent}}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-3 mb-0">
                                    <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th>{{trans('tickets.table.ticket')}}</th>
                                        <th>{{trans('tickets.table.status')}}</th>
                                        <th>{{trans('tickets.table.priority')}}</th>
                                        <th>{{trans('tickets.responsible')}}</th>
                                        <th>{{trans('tickets.table.deadline')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-gray-700 fw-semibold">
                                    @foreach($ticket->allChildren as $item)
                                        <tr>
                                            <td class="min-w-200px">
                                                <a href="{{route('cabinet.tickets.show', $item->id)}}" class="fw-bold text-gray-900 text-hover-primary" target="_blank">
                                                    #{{$ticket->id}}<i class="ki-duotone ki-right fs-6"></i>{{$item->id}}
                                                </a>
                                                <div class="text-gray-600 fw-normal">{{\Illuminate\Support\Str::limit(strip_tags($item->text), 80)}}</div>
                                            </td>
                                            <td><span class="badge badge-light-{{$item->status->color()}}">{{$item->status->label()}}</span></td>
                                            <td>
                                                @if($item->priority)
                                                    <span class="badge badge-light-{{$item->priority->class}}">{{$item->priority->getNameByLocale()}}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->performer)
                                                    {{$item->performer->name}}
                                                @else
                                                    <span class="text-muted">{{trans('tickets.sent.performer_empty')}}</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                @if($item->due_date)
                                                    @php $childActive = $item->status->is(\App\Enums\TicketStatusEnum::OPENED) || $item->status->is(\App\Enums\TicketStatusEnum::IN_PROGRESS); @endphp
                                                    <span class="{{$childActive && $item->isDue() ? 'text-danger fw-bold' : ''}}">{{$item->due_date->isoFormat('D MMM YYYY, HH:mm')}}</span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
