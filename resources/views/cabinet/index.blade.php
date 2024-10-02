@extends('layouts.app')
@section('title', 'Дашборд')
@section('content')
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-4 mb-10">
            <div class="card card-flush h-xl-100">
                <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-250px" style="background: linear-gradient(112.14deg, #3A7BD5 0%, #00D2FF 100%);" data-bs-theme="light">
                    <h3 class="card-title align-items-start flex-column text-white pt-15">
                        <span class="fw-bold fs-2x mb-3">Мои тикеты</span>
                        <div class="fs-4 text-white">
                            <span class="position-relative d-inline-block">
                                <a href="{{route('cabinet.tickets.inbox')}}" class="link-white opacity-75-hover text-gray-800 fw-bold d-block mb-1">
                                    Открытых тикетов: {{ $ticketCounts['opened'] }}
                                </a>
                                <span class="position-absolute opacity-50 bottom-0 start-0 border-2 border-body border-bottom w-100"></span>
                            </span>
                        </div>
                    </h3>
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
                                            {{ $ticketCounts['in_progress'] }}
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
                                            {{ $ticketCounts['done'] }}
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
                                            {{ $ticketCounts['completed'] }}
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
                                            {{ $ticketCounts['canceled'] }}
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

        <div class="col-xxl-4 mb-lg-5 mb-xl-10">
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
                            {{$done_tickets_count}}
                        </span>
                    </div>
                </div>

                @if($done_tickets_count > 0)
                    <div class="card-body d-flex align-items-end flex-row-fluid p-0">
                        <div class="card-rounded-bottom w-100" id="kt_charts_widget_44" data-kt-chart-color=primary style="height: 120px"></div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xxl-4 mb-lg-5 mb-xl-10">
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
@endsection

@push('vendor_css')
    <link href="{{asset('assets/css/plugins/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endpush

@push('vendor_js')
    <script src="{{asset('assets/js/widgets.bundle.js')}}"></script>
@endpush

{{--@push('modals')
    @include('partials.modals.tickets.create')
@endpush--}}

@push('custom_js')
    <script src="{{asset('assets/js/custom/tickets/create.js')}}"></script>

    <script>
        $(function() {
            var KTChartsWidget44 = {
                self: null,
                rendered: false,

                init: function() {
                    this.fetchData();
                },

                fetchData: function() {
                    var self = this;
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
                    var $chart = $('#kt_charts_widget_44');
                    if ($chart.length === 0) return;

                    var chartColor = $chart.data('kt-chart-color');
                    var height = $chart.height();
                    var grayColor = KTUtil.getCssVariableValue('--bs-gray-800');
                    var borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
                    var baseColor = KTUtil.getCssVariableValue('--bs-' + chartColor);
                    var lightColor = KTUtil.getCssVariableValue('--bs-' + chartColor + '-light');

                    var options = {
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
