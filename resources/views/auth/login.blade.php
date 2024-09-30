<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Ticket system</title>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="" />
    <meta property="og:site_name" content="" />
    <link rel="canonical" href="" />
    <link rel="shortcut icon" href="" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{asset('assets/css/plugins/bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/plugins/waitMe.min.css')}}" rel="stylesheet" type="text/css" />
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_body" class="app-blank app-blank">
{{--<div class="preloader">--}}
{{--    <div class="spinner-border text-primary" role="status"></div>--}}
{{--</div>--}}
<script>let defaultThemeMode = "light"; let themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>

<div class="d-flex flex-column flex-root" id="kt_app_root">
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-lg-row-fluid w-lg-50 bgi-size-cover bgi-position-center" style="background-image: url('{{asset('assets/media/auth/auth-bg.png')}}')">
            <div class="d-flex flex-column flex-center p-6 p-lg-10 w-100">
                <a href="index.html" class="mb-0 mb-lg-20">
                    <img alt="Logo" src="assets/media/logos/default-white.svg" class="h-40px h-lg-50px" />
                </a>
                <img class="d-none d-lg-block mx-auto w-300px w-lg-75 w-xl-500px mb-10 mb-lg-20" src="{{asset('assets/media/auth/auth-screens.png')}}" alt="" />
                <h1 class="d-none d-lg-block text-white fs-1qx fw-bold text-center mb-7">
                    Проблема. Действие. Результат.
                </h1>
            </div>
        </div>
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10">
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <div class="w-lg-500px p-10">
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="text-center mb-11">
                            <h1 class="text-gray-900 fw-bolder mb-3">Вход в кабинет</h1>
                        </div>
                        <div class="fv-row mb-8">
                            <input type="text"
                                   id="username"
                                   value="{{@old('username')}}"
                                   placeholder="Логин"
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
                                   placeholder="Пароль"
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
                                <span class="indicator-label">Войти</span>
                            </button>
                        </div>

                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                            <i class="ki-outline ki-information fs-2tx text-primary me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1 ">
                                <div class=" fw-semibold">
                                    <div class="fs-6 text-gray-700 ">
                                        Логин - это ваша учетная запись. <br> Пример: <strong>rmamedov, fpoladov, rjalalov</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
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
            $submitButton.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Проверяем...');
        });
    });
</script>
</body>
</html>



{{--{{dd(\LdapRecord\Models\ActiveDirectory\User::select(--}}
{{--    //'cn', 'name', 'username', 'samaccountname', 'mail', 'department'--}}
{{--    )--}}
{{--    ->where('samaccountname', '=', 'iamrahzadeh')--}}
{{--    ->where('department', '!=', '')--}}
{{--    //->where('useraccountcontrol', '!=', 514)--}}
{{--    ->limit(50)->get()->toArray())}}--}}

{{--{{dd(auth()->user())}}--}}

{{--{{dd(\App\Models\User::where('username', 'rkandiba')->get())}}--}}

{{--<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div>
        <x-input-label for="username" :value="__('Email')" />
        <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
        <x-input-error :messages="$errors->get('samaccountname')" class="mt-2" />
    </div>

    <!-- Password -->
    <div class="mt-4">
        <x-input-label for="password" :value="__('Password')" />

        <x-text-input id="password" class="block mt-1 w-full"
                      type="password"
                      name="password"
                      required autocomplete="current-password" />

        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Remember Me -->
    <div class="block mt-4">
        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
        </label>
    </div>

    <div class="flex items-center justify-end mt-4">
        @if (Route::has('password.request'))
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
        @endif

        <x-primary-button class="ms-3">
            {{ __('Log in') }}
        </x-primary-button>
    </div>
</form>--}}
