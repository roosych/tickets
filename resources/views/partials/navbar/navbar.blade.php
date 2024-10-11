<div class="app-navbar flex-stack flex-shrink-0 mt-lg-3" id="kt_app_aside_navbar">
    <div class="d-flex align-items-center me-2">
        @include('partials.navbar.user_menu')
        <div class="d-none d-lg-block m-0">
            <p class="text-gray-800 fs-4 fw-bold mb-0">
                {{auth()->user()->name}}
            </p>
        </div>
    </div>
    @include('partials.navbar.links')
</div>
