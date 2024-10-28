@extends('layouts.app')
@section('title', trans('common.dashboard'))
@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">{{trans('common.mainpage')}}</li>
    </ul>
@endsection
@section('content')

    <div class="card border-0 h-md-100 mb-10" data-bs-theme="light" style="background: linear-gradient(112.14deg, #00D2FF 0%, #3A7BD5 100%)">
        <div class="card-body">
            <div class="row align-items-center h-100">
                <div class="col-7 ps-xl-13">
                    <div class="text-white mb-6 pt-6">
                        <span class="fs-2qx fw-bold">
                            Есть вопрос? Сообщите нам!
                        </span>
                        <span class="fs-4 fw-semibold me-2 d-block lh-1 opacity-75">
                            Создайте тикет, и мы вам поможем!
                        </span>
                    </div>
                    <button class="btn btn-success flex-shrink-0 me-lg-2"
                            data-bs-toggle="modal"
                            data-bs-target="#kt_modal_new_ticket">
                        {{trans('tickets.table.create_ticket')}}
                    </button>
                </div>
                <div class="col-5 pt-10">
                    <div class="bgi-no-repeat bgi-size-contain bgi-position-x-end h-200px" style="background-image:url('{{asset('assets/media/misc/6.svg')}}')">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-10 p-lg-15">
            <div class="mb-13">
                <div class="mb-15">
                    <h4 class="fs-2x text-gray-800 w-bolder mb-6">
                        Часто задаваемые вопросы
                    </h4>
                    <p class="fw-semibold fs-4 text-gray-600 mb-2">
                        Здесь собраны самые частые технические вопросы сотрудников — от простых до самых неожиданных.
                        Прежде чем поднимать трубку, проверьте ответы, возможно, решение уже найдено, и вы сэкономите время себе и нашему IT-отделу
                    </p>
                </div>
                <div class="row mb-12">
                    <div class="col-md-6 pe-md-10 mb-10 mb-md-0">
                        <h2 class="text-gray-800 fw-bold mb-4">
                            Общие
                        </h2>

                        <div class="m-0">
                            <div class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                 data-bs-toggle="collapse"
                                 data-bs-target="#kt_job_4_2">
                                <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                    <i class="ki-outline ki-minus-square toggle-on text-primary fs-1"></i>
                                    <i class="ki-outline ki-plus-square toggle-off fs-1"></i>
                                </div>
                                <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                    Вопрос 1
                                </h4>
                            </div>
                            <div id="kt_job_4_2" class="collapse fs-6 ms-1">
                                <div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10">
                                    Ответ 1
                                </div>
                            </div>
                            <div class="separator separator-dashed"></div>
                        </div>

                        <div class="m-0">
                            <div class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                 data-bs-toggle="collapse"
                                 data-bs-target="#kt_job_4_3">
                                <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                    <i class="ki-outline ki-minus-square toggle-on text-primary fs-1"></i>
                                    <i class="ki-outline ki-plus-square toggle-off fs-1"></i>
                                </div>
                                <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">Вопрос 2</h4>
                            </div>
                            <div id="kt_job_4_3" class="collapse fs-6 ms-1">
                                <div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10">
                                    Ответ 2
                                </div>
                            </div>
                            <div class="separator separator-dashed"></div>
                        </div>
                    </div>

                    <div class="col-md-6 ps-md-10">
                        <h2 class="text-gray-800 fw-bold mb-4">
                            По тикетам
                        </h2>
                        <div class="m-0">
                            <div class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                 data-bs-toggle="collapse"
                                 data-bs-target="#kt_job_5_2">
                                <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                    <i class="ki-outline ki-minus-square toggle-on text-primary fs-1"></i>
                                    <i class="ki-outline ki-plus-square toggle-off fs-1"></i>
                                </div>
                                <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                    Вопрос 1
                                </h4>
                            </div>
                            <div id="kt_job_5_2" class="collapse fs-6 ms-1">
                                <div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10">
                                    Ответ 1
                                </div>
                            </div>
                            <div class="separator separator-dashed"></div>
                        </div>

                        <div class="m-0">
                            <div class="d-flex align-items-center collapsible py-3 toggle collapsed mb-0"
                                 data-bs-toggle="collapse"
                                 data-bs-target="#kt_job_5_3">
                                <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                    <i class="ki-outline ki-minus-square toggle-on text-primary fs-1"></i>
                                    <i class="ki-outline ki-plus-square toggle-off fs-1"></i>
                                </div>
                                <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                    Вопрос 2
                                </h4>
                            </div>
                            <div id="kt_job_5_3" class="collapse fs-6 ms-1">
                                <div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10">
                                    Ответ 2
                                </div>
                            </div>
                            <div class="separator separator-dashed"></div>
                        </div>
                    </div>
                </div>
            </div>

{{--            <div class="card mb-4 bg-light text-center">
                <div class="card-body py-12">
                    <a href="#" class="mx-4">
                        <img src="" class="h-30px my-2" alt="" />
                    </a>
                </div>
            </div>--}}
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
