@php($languages = \App\Models\Language::getActive())

<div class="app-navbar-item me-1 me-lg-5" id="kt_header_user_menu_toggle">
    <div class="cursor-pointer symbol symbol-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
        <img src="{{auth()->user()->avatar}}" alt="user" />
    </div>
    <div class="d-none d-lg-block ms-4">
        <p class="text-gray-800 fs-4 fw-bold mb-0">
            {{auth()->user()->name}}
        </p>
    </div>
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
        <div class="menu-item px-3">
            <div class="menu-content d-flex align-items-center px-3">
                <div class="symbol symbol-50px me-5">
                    <img alt="{{auth()->user()->name}}" src="{{auth()->user()->avatar}}" />
                </div>
                <div class="d-flex flex-column">
                    <div class="fw-bold d-flex align-items-center fs-5">
                        {{auth()->user()->name}}
                    </div>
                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">
                        {{auth()->user()->email}}
                    </a>
                </div>
            </div>
        </div>
        <div class="separator my-2"></div>
        <div class="menu-item px-5">
            <a href="{{route('cabinet.tickets.inbox')}}" class="menu-link px-5">
                <span class="menu-text">{{trans('common.user_menu.my_tickets')}}</span>
            </a>
        </div>
        <div class="separator my-2"></div>
        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
            <a href="#" class="menu-link px-5">
                <span class="menu-title position-relative">{{trans('common.user_menu.theme')}}
                <span class="ms-5 position-absolute translate-middle-y top-50 end-0">
                    <i class="ki-outline ki-night-day theme-light-show fs-2"></i>
                    <i class="ki-outline ki-moon theme-dark-show fs-2"></i>
                </span></span>
            </a>
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px" data-kt-menu="true" data-kt-element="theme-mode-menu">
                <div class="menu-item px-3 my-0">
                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                        <span class="menu-icon" data-kt-element="icon">
                            <i class="ki-outline ki-night-day fs-2"></i>
                        </span>
                        <span class="menu-title">{{trans('common.user_menu.light')}}</span>
                    </a>
                </div>
                <div class="menu-item px-3 my-0">
                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                        <span class="menu-icon" data-kt-element="icon">
                            <i class="ki-outline ki-moon fs-2"></i>
                        </span>
                        <span class="menu-title">{{trans('common.user_menu.dark')}}</span>
                    </a>
                </div>
                <div class="menu-item px-3 my-0">
                    <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                        <span class="menu-icon" data-kt-element="icon">
                            <i class="ki-outline ki-screen fs-2"></i>
                        </span>
                        <span class="menu-title">{{trans('common.user_menu.system')}}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
            <a href="#" class="menu-link px-5">
                <span class="menu-title position-relative">{{trans('common.user_menu.languages')}}
                    <span class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0">
                        {{$languages->firstWhere('id', app()->getLocale())?->name}}
                        <img class="w-15px h-15px rounded-1 ms-2" src="{{asset('assets/media/flags/'.app()->getLocale().'.svg')}}" alt="" />
                    </span>
                </span>
            </a>
            <div class="menu-sub menu-sub-dropdown w-175px py-4">
                @foreach($languages as $language)
                    <div class="menu-item px-3">
                        <a href="{{route('language', $language->id)}}" class="menu-link d-flex px-5 {{$language->id === app()->getLocale() ? 'active' : ''}}">
                            <span class="symbol symbol-20px me-4">
                                <img class="rounded-1" src="{{asset('assets/media/flags/'.$language->id.'.svg')}}" alt="" />
                            </span>
                            {{$language->name}}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="menu-item px-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" href="#" class="btn btn-sm w-100 menu-link px-5 fs-6">{{trans('common.user_menu.logout')}}</button>
            </form>
        </div>
    </div>
</div>
