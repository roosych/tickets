<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Ticket approval</title>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}" />
    <meta property="og:type" content="" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <link rel="canonical" href="" />
    <link rel="shortcut icon" href="{{asset('assets/media/favicon.png')}}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    @stack('vendor_css')
    <link href="{{asset('assets/css/plugins/bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/waitMe.min.css')}}" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank app-blank bgi-size-cover bgi-position-center bgi-no-repeat">

<script>let defaultThemeMode = "light"; let themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>

<div class="loader-overlay" id="loaderOverlay">
    <div class="loader"></div>
</div>
<div class="d-flex flex-column flex-root" id="kt_app_root">
    <style>body { background-image: url('{{asset('assets/media/misc/bg11.jpg')}}'); } [data-bs-theme="dark"] body { background-image: url('{{asset('assets/media/misc/bg11-dark.jpg')}}'); }</style>
    <div class="d-flex flex-column flex-center p-10 mt-10">
        <div class="mb-10">
            <a href="{{url('/')}}" class="">
                <img alt="Logo" src="{{asset('assets/media/logo.svg')}}" class="h-40px">
            </a>
        </div>
        <div class="card card-flush pt-3 mb-5 mb-lg-10">
            @if(! $approvalRequest->status->is(\App\Enums\TicketApprovalRequestStatusEnum::PENDING))
                <div class="card-body">
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed min-w-lg-600px flex-shrink-0 p-6">
                        <i class="ki-outline ki-information-3 fs-2tx text-primary me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                            <div class="mb-3 mb-md-0 fw-semibold">
                                <h4 class="text-gray-900 fw-bold">
                                    Запрос на одобрение обработан!
                                </h4>
                                <div class="fs-6 text-gray-700 pe-7">
                                    Вы приняли решение по этой заявке.
                                    <div>
                                        Статус:
                                        <span class="fw-bold text-{{ $approvalRequest->status->color() }}">
                                            {{ $approvalRequest->status->label() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{url('/')}}" class="btn btn-primary px-6 align-self-center text-nowrap">
                                {{trans('common.mainpage')}}
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="fw-bold">
                            Ticket <a href="{{route('cabinet.tickets.show', $ticket)}}" class="text-gray-800 text-hover-primary" target="_blank">
                                #{{$ticket->id}}
                            </a>
                        </h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-5">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="ki-outline ki-calendar fs-2 me-1"></i>
                                <span class="fw-semibold text-muted text-end me-3">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->isoFormat('D MMMM, HH:mm') }}
                            </span>
                            </div>

                            <div class="d-flex">
                                <div class="d-flex">
                                    <div class="text-gray-800 fw-bold fs-12">
                                        {{trans('tickets.table.priority')}}:
                                    </div>
                                    <span class="badge badge-light-{{$ticket->priority->class}} ms-2 fw-bold fs-7">
                                    {{$ticket->priority->getNameByLocale()}}
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-lg badge-light-secondary mb-4">
                        <div class="d-flex align-items-center flex-wrap">
                            {{$ticket->creator->department}}
                            <i class="ki-outline ki-right fs-2 text-gray-800 mx-1"></i>
                            {{$ticket->department->name}}
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="d-flex text-gray-800 fw-bold fs-12 mb-3 ms-1">
                                Автор тикета:
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    @if($ticket->creator->avatar)
                                        <div class="symbol-label cursor-default">
                                            <img src="{{$ticket->creator->avatar}}" alt="{{$ticket->creator->name}}" class="w-100" />
                                        </div>
                                    @else
                                        <div class="symbol-label fs-3 bg-light-dark text-gray-800">
                                            {{get_initials($ticket->creator->name)}}
                                        </div>
                                    @endif
                                </div>

                                <div class="pe-5">
                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                        <p class="fw-bold text-gray-900 mb-0">
                                            {{$ticket->creator->name}}
                                        </p>
                                    </div>
                                    <div>
                                    <span class="text-muted fw-semibold">
                                        {{$ticket->creator->email}}
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex text-gray-800 fw-bold fs-12 mb-3 ms-1">
                                Запросил(а) одобрение:
                            </div>
                            <div class="performers_symbols symbol-group symbol-hover flex-nowrap">
                                <div class="performer_wrapper">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                            @if($approvalRequest->creator->avatar)
                                                <div class="symbol-label cursor-default">
                                                    <img src="{{$approvalRequest->creator->avatar}}" alt="{{$approvalRequest->creator->name}}" class="w-100" />
                                                </div>
                                            @else
                                                <div class="symbol-label fs-3 bg-light-dark text-gray-800">
                                                    {{get_initials($approvalRequest->creator->name)}}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="pe-5">
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <p class="fw-bold text-gray-900 mb-0">
                                                    {{$approvalRequest->creator->name}}
                                                </p>
                                            </div>
                                            <div>
                                            <span class="text-muted fw-semibold">
                                                {{$approvalRequest->creator->email}}
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-500 fw-semibold fs-5 my-10">
                        {{$ticket->text}}
                    </div>
                    @if($approvalRequest->status->is(\App\Enums\TicketApprovalRequestStatusEnum::PENDING))
                        @include('partials.notices.ticket-approve-request-actions')
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script src="{{asset('assets/js/plugins/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
<script src="{{asset('assets/js/plugins/waitMe.min.js')}}"></script>
<script src="{{asset('assets/js/custom/main.js')}}"></script>
@if($approvalRequest->status->is(\App\Enums\TicketApprovalRequestStatusEnum::PENDING))
<script>
    $(function() {
        $('a.approve, a.reject').on('click', function(e) {
            e.preventDefault();
            applyWait($('body'));
            let $btn = $(this);
            let url = $btn.attr('href');
            $('a.approve, a.reject').prop('disabled', true);

            $.get(url, function(response) {
                removeWait($('body'));
                Swal.fire({
                    title: '{{trans('common.swal.success_title')}}',
                    icon: 'success',
                }).then(() => {
                    window.location.reload();
                });

            }).fail(function(xhr) {
                removeWait($('body'));
                Swal.fire('{{trans('common.swal.error_title')}}', xhr.responseText || '{{trans('common.swal.error_text')}}', 'error');
                $('a.approve, a.reject').prop('disabled', false);
            });
        });
    });
</script>
@endif

</body>
</html>
