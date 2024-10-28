<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="100px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo d-none d-lg-flex flex-center pt-8 mb-3" id="kt_app_sidebar_logo">
        <a href="{{route('cabinet.index')}}">
            <img alt="Logo" src="{{asset('assets/media/logo.svg')}}" class="h-35px" />
        </a>
    </div>
    <div class="app-sidebar-menu d-flex flex-center overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper d-flex hover-scroll-overlay-y scroll-ms my-5" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu, #kt_app_sidebar" data-kt-scroll-offset="5px">
            <div class="menu menu-column menu-rounded menu-active-bg menu-title-gray-700 menu-arrow-gray-500 menu-icon-gray-500 menu-bullet-gray-500 menu-state-primary my-auto" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
                <div class="menu-item {{active_link(['cabinet.index'])}} py-2">
                    <span class="menu-link menu-center">
                        <a href="{{route('cabinet.index')}}">
                            <span class="menu-icon me-0">
                                <i class="ki-outline ki-home fs-2x"></i>
                            </span>
                        </a>
                    </span>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'click'}" data-kt-menu-placement="right-start" class="menu-item {{active_link(['cabinet.tickets*', 'cabinet.tags*'])}} py-2">
                    <span class="menu-link menu-center">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-mouse-circle fs-2x"></i>
                        </span>
                    </span>
                    <div class="menu-sub menu-sub-dropdown px-2 py-4 w-200px w-lg-225px mh-75 overflow-auto">
                        <div class="menu-item">
                            <div class="menu-content">
                                <span class="menu-section fs-5 fw-bolder ps-1 py-1">
                                    {{trans('sidebar.tickets.title')}}
                                </span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{route('cabinet.tickets.index')}}"
                               title="{{trans('sidebar.tickets.dept.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">
                                    {{trans('sidebar.tickets.dept.text')}}
                                </span>
                            </a>
                            <a class="menu-link" href="{{route('cabinet.tickets.inbox')}}"
                               title="{{trans('sidebar.tickets.my.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">
                                    {{trans('sidebar.tickets.my.text')}}
                                </span>
                            </a>
                            <a class="menu-link" href="{{route('cabinet.tickets.sent')}}"
                               title="{{trans('sidebar.tickets.sent.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">
                                    {{trans('sidebar.tickets.sent.text')}}
                                </span>
                            </a>
                            <a class="menu-link" href="{{route('cabinet.tags.index')}}"
                               title="{{trans('sidebar.tickets.tags.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">
                                    {{trans('sidebar.tickets.tags.text')}}
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->getDepartment()->active)
                    <div data-kt-menu-trigger="{default: 'click', lg: 'click'}" data-kt-menu-placement="right-start" class="menu-item {{active_link(['cabinet.dept*', 'cabinet.users*', 'cabinet.reports*'])}} py-2">
                    <span class="menu-link menu-center">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-flag fs-2x"></i>
                        </span>
                    </span>
                        <div class="menu-sub menu-sub-dropdown px-2 py-4 w-200px w-lg-225px mh-75 overflow-auto">
                            <div class="menu-item">
                                <div class="menu-content">
                                    <span class="menu-section fs-5 fw-bolder ps-1 py-1">
                                        Мой отдел
                                    </span>
                                </div>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="{{route('cabinet.dept.users.index')}}"
                                   title="{{trans('sidebar.dept.users.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">
                                        {{trans('sidebar.dept.users.text')}}
                                    </span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="{{route('cabinet.dept.roles')}}"
                                   title="{{trans('sidebar.dept.access.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">
                                        {{trans('sidebar.dept.access.text')}}
                                    </span>
                                </a>
                            </div>
                            @can('users', 'report')
                                <div class="menu-item">
                                    <a class="menu-link" href="{{route('cabinet.reports.tickets')}}"
                                       title="{{trans('sidebar.dept.reports.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                        <span class="menu-title">
                                        {{trans('sidebar.dept.reports.text')}}
                                    </span>
                                    </a>
                                </div>
                            @endcan

                        </div>
                    </div>

                    {{--<div data-kt-menu-trigger="{default: 'click', lg: 'click'}" data-kt-menu-placement="right-start" class="menu-item {{active_link(['cabinet.reports*', 'cabinet.reports*'])}} py-2">
                    <span class="menu-link menu-center">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-chart-line-up fs-2x"></i>
                        </span>
                    </span>
                        <div class="menu-sub menu-sub-dropdown px-2 py-4 w-200px w-lg-225px mh-75 overflow-auto">
                            <div class="menu-item">
                                <div class="menu-content">
                                    <span class="menu-section fs-5 fw-bolder ps-1 py-1">
                                        {{trans('sidebar.stats.title')}}
                                    </span>
                                </div>
                            </div>

                            --}}{{--<div class="menu-item">
                                <a class="menu-link" href="{{route('cabinet.reports.depts')}}"
                                   title="{{trans('sidebar.stats.dept.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">
                                        {{trans('sidebar.stats.dept.text')}}
                                    </span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="{{route('cabinet.reports.tags')}}"
                                   title="{{trans('sidebar.stats.tags.hint')}}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">
                                        {{trans('sidebar.stats.tags.text')}}
                                    </span>
                                </a>
                            </div>--}}{{--
                        </div>
                    </div>--}}

                    <div data-kt-menu-trigger="{default: 'click', lg: 'click'}" data-kt-menu-placement="right-start" class="menu-item {{active_link(['cabinet.settings*'])}} py-2">
                    <span class="menu-link menu-center">
                        <span class="menu-icon me-0">
                            <i class="ki-outline ki-wrench fs-2x"></i>
                        </span>
                    </span>
                        <div class="menu-sub menu-sub-dropdown px-2 py-4 w-200px w-lg-225px mh-75 overflow-auto">
                            <div class="menu-item">
                                <div class="menu-content">
                                    <span class="menu-section fs-5 fw-bolder ps-1 py-1">
                                        Настройки
                                    </span>
                                </div>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link" href="{{route('cabinet.settings.users')}}"
                                   title="Пользователи Active Directory" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss="click" data-bs-placement="right">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">
                                        Пользователи
                                    </span>
                                </a>
                            </div>

                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
