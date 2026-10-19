                    <div class="row g-5 mt-2">
                        <div class="col-md-6">
                            <div class="border border-dashed border-gray-300 rounded p-5 h-100">
                                <div class="fw-bold text-gray-800 mb-4">{{trans('tickets.deadline_block.title')}}</div>
                                @if($ticket->due_date)
                                    <div class="d-flex justify-content-between fw-semibold mb-3">
                                        <span class="text-muted">
                                            <i class="ki-outline ki-calendar fs-3 me-1"></i>{{$ticket->created_at->isoFormat('D MMM YYYY, HH:mm')}}
                                        </span>
                                        <span class="text-danger fw-bold">
                                            <i class="ki-outline ki-calendar-remove text-danger fs-3 me-1"></i>{{$ticket->due_date->isoFormat('D MMM YYYY, HH:mm')}}
                                        </span>
                                    </div>

                                    @if($st->is(\App\Enums\TicketStatusEnum::DONE) || $st->is(\App\Enums\TicketStatusEnum::COMPLETED))
                                        @if($ticket->isCompletedInTime())
                                            <div class="text-success fw-bold fs-6">
                                                <i class="ki-outline ki-check-circle text-success fs-2 me-1"></i>{{trans('tickets.deadline_block.in_time')}}
                                            </div>
                                        @elseif($ticket->isCompletedLate())
                                            <div class="text-danger fw-bold fs-6">
                                                <i class="ki-outline ki-cross-circle text-danger fs-2 me-1"></i>{{trans('tickets.deadline_block.late')}}
                                            </div>
                                        @endif
                                    @elseif(! $st->is(\App\Enums\TicketStatusEnum::CANCELED))
                                        <div class="h-8px bg-light rounded mb-3">
                                            <div class="bg-{{$ticket->isDue() ? 'danger' : 'success'}} rounded h-8px" role="progressbar"
                                                 style="width: {{ $ticket->dueProgress() }}%;"
                                                 aria-valuenow="{{$ticket->dueProgress()}}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="fw-semibold {{ $ticket->isDue() ? 'text-danger' : 'text-gray-600' }}">
                                            @if($ticket->isDue())
                                                {{trans('tickets.deadline_expired')}}
                                            @else
                                                {{trans('tickets.deadline_after')}} {{ $ticket->timeUntilDueFull() }}.
                                            @endif
                                            @if($canChangeDeadline)
                                                <a href="javascript:void(0);" class="ms-2 d-inline"
                                                   data-ticket_id="{{$ticket->id}}"
                                                   data-id="{{$ticket->id}}"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#update_deadline_modal">
                                                    {{trans('tickets.deadline_change_link')}}
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="text-muted">
                                        <i class="ki-outline ki-calendar fs-3 me-1"></i>{{$ticket->created_at->isoFormat('D MMM YYYY, HH:mm')}}
                                    </div>
                                    <div class="text-gray-600 mt-2">{{trans('tickets.deadline_block.none')}}</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border border-dashed border-gray-300 rounded p-5 h-100">
                                <div class="fw-bold text-gray-800 mb-4">{{trans('tickets.time_block.title')}}</div>
                                @php
                                    $timeline = [
                                        [trans('tickets.time_block.created'), $ticket->created_at, null],
                                        [trans('tickets.time_block.taken'), $firstTakenAt, $reactionMin !== null ? trans('tickets.time_block.reaction', ['time' => formatMinutes($reactionMin)]) : null],
                                        [trans('tickets.time_block.done'), $doneAt, $workMin !== null ? trans('tickets.time_block.work', ['time' => formatMinutes($workMin)]) : null],
                                        [trans('tickets.time_block.closed'), $closedAt, null],
                                        [trans('tickets.time_block.canceled'), $canceledAt, null],
                                    ];
                                @endphp
                                @foreach($timeline as [$label, $at, $note])
                                    @if($at)
                                        <div class="d-flex justify-content-between fs-6 mb-2">
                                            <span class="text-gray-600">{{$label}}</span>
                                            <span class="text-gray-800 fw-semibold text-end">
                                                {{$at->isoFormat('D MMM YYYY, HH:mm')}}
                                                @if($note)<span class="d-block text-muted fs-8 fw-normal">{{$note}}</span>@endif
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span class="text-gray-800">{{$finishedAt ? trans('tickets.time_block.total') : trans('tickets.time_block.in_system')}}</span>
                                    <span class="text-gray-800">{{formatMinutes($totalMin)}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
