@extends('layouts.app')
@section('title', 'Дашборд')
@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">Главная</li>
    </ul>
@endsection
@section('content')
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-4">
            <div class="col-12">
                <div class="card card-flush h-xl-100">
                    <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-250px" style="background: linear-gradient(112.14deg, #3A7BD5 0%, #00D2FF 100%);" data-bs-theme="light">
                        <h3 class="card-title align-items-start flex-column text-white pt-15">
                            <span class="fw-bold fs-2x mb-3">
                                Мои тикеты
                            </span>
                            <div class="fs-4 text-white">
                            <span class="position-relative d-inline-block">
                                <a href="{{route('cabinet.tickets.inbox')}}" class="link-white opacity-75-hover text-gray-800 fw-bold d-block mb-1">
                                    Открытых тикетов: {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::OPENED) }}
                                </a>
                                <span class="position-absolute opacity-50 bottom-0 start-0 border-2 border-body border-bottom w-100"></span>
                            </span>
                            </div>
                        </h3>

                        <div class="card-toolbar pt-5">
                            <button class="btn btn-icon bg-white btn-circle"
                                data-bs-toggle="modal"
                                data-bs-target="#kt_modal_new_ticket">
                                <i class="ki-outline ki-plus fs-1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body mt-n20">
                        <div class="mt-n20 position-relative">
                            <div class="row g-3 g-lg-6">
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                    <span class="symbol-label">
                                        <i class="ki-outline ki-timer fs-1 text-primary"></i>
                                    </span>
                                        </div>

                                        <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                            {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::IN_PROGRESS) }}
                                        </span>
                                            <span class="text-gray-500 fw-semibold fs-6">
                                            В процессе
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-outline ki-check-circle fs-1 text-primary"></i>
                                        </span>
                                        </div>
                                        <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                            {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::DONE) }}
                                        </span>
                                            <span class="text-gray-500 fw-semibold fs-6">
                                            Выполненных
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                <span class="symbol-label">
                                    <i class="ki-outline ki-double-check-circle fs-1 text-primary"></i>
                                </span>
                                        </div>
                                        <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                            {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::COMPLETED) }}
                                        </span>
                                            <span class="text-gray-500 fw-semibold fs-6">
                                            Завершенных
                                        </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <div class="symbol symbol-30px me-5 mb-8">
                                <span class="symbol-label">
                                    <i class="ki-outline ki-cross-circle fs-1 text-primary"></i>
                                </span>
                                        </div>
                                        <div class="m-0">
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1">
                                            {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::CANCELED) }}
                                        </span>
                                            <span class="text-gray-500 fw-semibold fs-6">
                                            Отмененных
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card card-flush mb-5 mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-row-fluid flex-stack">
                                <div class="d-flex flex-column">
                                    <p class="mb-2 fw-bold text-gray-800 fs-3">
                                        Выполнено тикетов
                                    </p>
                                    <span class="fs-6 text-gray-500 fs-semibase">
                                за всё время
                            </span>
                                </div>
                                <span class="text-gray-800 fw-bold fs-2x">
                            {{ auth()->user()->getTicketsCountByStatus(\App\Enums\TicketStatusEnum::DONE) }}
                        </span>
                            </div>
                        </div>

                        <div class="card-body d-flex align-items-end flex-row-fluid p-0">
                            <div class="card-rounded-bottom w-100" id="kt_charts_widget_44"
                                 data-kt-chart-color=success style="height: 119px"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card card-flush mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">
                            {{ number_format($totalTickets, 0, '.', ',') }}
                        </span>
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">
                            Тикеты отдела
                        </span>
                            </div>
                        </div>
                        @if(count($topPerformers))
                            <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">
                        Топ 3 исполнителей
                    </span>
                                <div class="symbol-group symbol-hover flex-nowrap">
                                    @foreach($topPerformers as $user)
                                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="{{$user->name}}">
                                            <img alt="{{$user->name}}" src="{{$user->avatar}}" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/filepond.min.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/widgets.bundle.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.min.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond.jquery.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-type.js')}}"></script>
    <script src="{{asset('assets/js/plugins/filepond-plugin-file-validate-size.js')}}"></script>
@endpush

@push('modals')
    @include('partials.modals.tickets.create')
@endpush

@push('custom_js')
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>

    <script>
        //filepond
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        $('.my-pond').filepond({
            server: {
                process: {
                    url: '{{ route('cabinet.files.upload') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    onload: (response) => {
                        let responseJson = JSON.parse(response);
                        if (responseJson.folder) {
                            // Получаем существующий массив папок из localStorage
                            let uploadedFolders = JSON.parse(localStorage.getItem('uploadedFolders')) || [];
                            // Добавляем новую папку в массив
                            uploadedFolders.push(responseJson.folder);
                            // Сохраняем обновленный массив в localStorage
                            localStorage.setItem('uploadedFolders', JSON.stringify(uploadedFolders));
                        }
                    },
                    onerror: (response) => {
                        console.error('Ошибка загрузки файла:', response);
                    }
                },
                revert: {
                    url: '{{ route('cabinet.files.delete') }}',
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            },
            allowMultiple: true,
            acceptedFileTypes: [
                'image/png',
                'image/jpg',
                'image/jpeg',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            labelFileTypeNotAllowed: 'Не поддерживаемый тип файла',
            maxFileSize: '5MB',
            labelMaxFileSizeExceeded: 'Файл слишком большой',
            labelIdle: 'Перетащите файлы сюда или нажмите, чтобы загрузить.'
        });

        let token = $('meta[name="_token"]').attr('content');

        //create ticket
        $('#create_ticket_form_submit').click(function (e) {
            e.preventDefault();
            let form = $('#kt_modal_new_ticket_form');
            let button = $(this);
            applyWait($('body'));
            $.ajax({
                url: "{{route('cabinet.tickets.store')}}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: form.serialize(),
                success: function (response) {
                    if(response.status === 'success') {
                        removeWait($('body'));
                        Swal.fire('Все прошло успешно!', '{{trans('common.swal.success_text')}}', 'success');
                        window.location.href = '{{route('cabinet.tickets.sent')}}';
                    } else {
                        removeWait($('body'));
                        Swal.fire('Произошла ошибка!', 'common.swal.error_text', 'error');
                    }
                },
                error: function (response) {
                    let errorMessage = '';
                    if (response.status === 422) {
                        const errors = response.responseJSON.errors;
                        for (const key in errors) {
                            errorMessage += `<p class="mb-0">${errors[key][0]}</p>`;
                        }
                    }
                    removeWait($('body'));
                    Swal.fire('Произошла ошибка!', errorMessage, 'error');
                },
            });
        });
    </script>

    <script>
        $(function() {
            var KTChartsWidget44 = {
                self: null,
                rendered: false,

                init: function() {
                    this.fetchData();
                },

                fetchData: function() {
                    let self = this;
                    $.ajax({
                        url: '{{route('cabinet.get_tickets_chart')}}',
                        method: 'GET',
                        success: function(response) {
                            self.render(response.data);
                        },
                        error: function(xhr, status, error) {
                            console.error("Ошибка при получении данных:", error);
                        }
                    });
                },

                render: function(chartData) {
                    let $chart = $('#kt_charts_widget_44');
                    if ($chart.length === 0) return;

                    let chartColor = $chart.data('kt-chart-color');
                    let height = $chart.height();
                    let grayColor = KTUtil.getCssVariableValue('--bs-gray-800');
                    let borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
                    let baseColor = KTUtil.getCssVariableValue('--bs-' + chartColor);
                    let lightColor = KTUtil.getCssVariableValue('--bs-' + chartColor + '-light');

                    let options = {
                        series: [{
                            name: "Тикетов",
                            data: chartData
                        }],
                        chart: {
                            fontFamily: "inherit",
                            type: "area",
                            height: height,
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            sparkline: { enabled: true }
                        },
                        plotOptions: {},
                        legend: { show: false },
                        dataLabels: { enabled: false },
                        fill: { type: "solid", opacity: 1 },
                        stroke: { curve: "smooth", show: true, width: 3, colors: [baseColor] },
                        xaxis: {
                            categories: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: { show: false, style: { colors: grayColor, fontSize: "12px" } },
                            crosshairs: { show: false, position: "front", stroke: { color: borderColor, width: 1, dashArray: 3 } },
                            tooltip: { enabled: true, formatter: undefined, offsetY: 0, style: { fontSize: "12px" } }
                        },
                        yaxis: {
                            min: 0,
                            max: Math.max(...chartData) * 1.2, // максимум на 20% выше наибольшего значения
                            labels: { show: false, style: { colors: grayColor, fontSize: "12px" } }
                        },
                        states: {
                            normal: { filter: { type: "none", value: 0 } },
                            hover: { filter: { type: "none", value: 0 } },
                            active: { allowMultipleDataPointsSelection: false, filter: { type: "none", value: 0 } }
                        },
                        tooltip: {
                            style: { fontSize: "12px" },
                            y: {
                                formatter: function(val) {
                                    return val;
                                }
                            }
                        },
                        colors: [lightColor],
                        markers: { colors: lightColor, strokeColor: baseColor, strokeWidth: 3 }
                    };

                    this.self = new ApexCharts($chart[0], options);

                    this.self.render();
                    this.rendered = true;

                    this.bindThemeModeChange();
                },

                bindThemeModeChange: function() {
                    var self = this;
                    $('body').on('kt.thememode.change', function() {
                        if (self.rendered) {
                            self.self.destroy();
                        }
                        self.fetchData();
                    });
                }
            };

            KTChartsWidget44.init();
        });
    </script>
    @stack('js_from_modal')
@endpush
