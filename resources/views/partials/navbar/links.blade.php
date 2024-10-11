<div class="app-navbar-item">
    <div class="btn btn-icon btn-custom btn-light-info btn-active-light-info w-40px h-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
        <i class="ki-outline ki-element-11 fs-1"></i>
    </div>
    <div class="menu menu-sub menu-sub-dropdown menu-column w-250px w-lg-325px" data-kt-menu="true">
        <div class="d-flex flex-column flex-center bgi-no-repeat rounded-top px-9 py-10" style="background-image:url('{{asset('assets/media/misc/menu-header-bg.jpg')}}')">
            <h3 class="text-white fw-semibold mb-3">{{trans('common.user_menu.fast_links')}}</h3>
        </div>
        <div class="row g-0">

            <div class="col-6">
                <a href="https://mx.metak.az" target="_blank" class="d-flex flex-column flex-center h-100 p-6 bg-hover-light border-bottom">
                    <i class="ki-outline ki-sms fs-3x text-primary mb-2"></i>
                    <span class="fs-5 fw-semibold text-gray-800 mb-0">{{trans('common.user_menu.link_mail')}}</span>
                </a>
            </div>
            <div class="col-6">
                <a href="https://intranet.metak.az" target="_blank" class="d-flex flex-column flex-center h-100 p-6 bg-hover-light border-end">
                    <i class="ki-outline ki-abstract-41 fs-3x text-primary mb-2"></i>
                    <span class="fs-5 fw-semibold text-gray-800 mb-0">{{trans('common.user_menu.link_intranet')}}</span>
                </a>
            </div>

        </div>
    </div>

</div>
