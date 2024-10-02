@extends('layouts.app')
@section('title', 'Дашборд')
@section('breadcrumbs')
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <li class="breadcrumb-item text-muted">Главная</li>
    </ul>
@endsection
@section('content')
    Deactive department user dashboard
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
@endpush
