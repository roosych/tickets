@php
    $languages = \App\Models\Language::getActive();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Ticket system</title>
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
    <link href="{{asset('assets/css/plugins/bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/waitMe.min.css')}}" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank app-blank">
{{--<div class="preloader">--}}
{{--    <div class="spinner-border text-primary" role="status"></div>--}}
{{--</div>--}}
<script>let defaultThemeMode = "light"; let themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>

<div class="d-flex flex-column flex-root" id="kt_app_root">
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center" style="background-image: url('{{asset('assets/media/auth/auth-bg.png')}}')">
            <div class="d-flex flex-column flex-center p-6 p-lg-10 w-100">
                <img class="d-none d-lg-block mx-auto w-300px w-lg-75 w-xl-700px mb-10 mb-lg-20" src="{{asset('assets/media/auth/auth-screens.png')}}" alt="" />
                <h1 class="d-none d-lg-block text-white fs-1qx fw-bold text-center mb-7">
                    {{trans('auth.slogan')}}
                </h1>
            </div>
        </div>
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10">
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <div class="w-lg-500px p-10">
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="text-center mb-11">
                            <h1 class="text-gray-900 fw-bolder mb-3">
                                {{trans('auth.title')}}
                            </h1>
                            <div class="text-gray-500 fw-semibold fs-6">
                                {{trans('auth.subtitle')}}
                            </div>
                        </div>
                        <div class="fv-row mb-8">
                            <input type="text"
                                   id="username"
                                   value="{{@old('username')}}"
                                   placeholder="{{trans('auth.login_placeholder')}}"
                                   name="username"
                                   autocomplete="off"
                                   autofocus
                                   class="form-control bg-transparent" />
                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                @foreach ((array) $errors->get('username') as $message)
                                    <p class="mb-0">{{ $message }}</p>
                                @endforeach
                                @foreach ((array) $errors->get('samaccountname') as $message)
                                    <p class="mb-0">{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="fv-row mb-3">
                            <input type="password"
                                   placeholder="{{trans('auth.password_placeholder')}}"
                                   name="password"
                                   autocomplete="off"
                                   class="form-control bg-transparent" />
                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                @foreach ((array) $errors->get('password') as $message)
                                    <p class="mb-0">{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>
                        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                            <div></div>
                        </div>
                        <div class="d-grid mb-10">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                <span class="indicator-label">
                                    {{trans('auth.login')}}
                                </span>
                            </button>
                        </div>

                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                            <i class="ki-outline ki-information fs-2tx text-primary me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1 ">
                                <div class=" fw-semibold">
                                    <div class="fs-6 text-gray-700 ">
                                        {{trans('auth.hint_text')}} <br> {{trans('auth.example')}} <strong>rmamedov, fpoladov, rjalalov</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <div class="d-flex flex-center flex-wrap px-5">
                <div class="d-flex fw-semibold text-primary fs-base">
                    @foreach($languages as $lang)
                        <a href="{{route('language', $lang->id)}}" class="px-5">
                            <span class="symbol symbol-20px me-1">
                                <img class="rounded-1" src="{{asset('assets/media/flags/'.$lang->id.'.svg')}}" alt="" />
                            </span>
                            {{$lang->name}}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{asset('assets/js/plugins/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
<script src="{{asset('assets/js/plugins/waitMe.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#kt_sign_in_form').on('submit', function() {
            const $submitButton = $('#kt_sign_in_submit');
            $submitButton.prop('disabled', true);
            $submitButton.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> {{trans('auth.loader')}}');
        });
    });
</script>
</body>
</html>
