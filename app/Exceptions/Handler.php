<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Устаревший CSRF-токен в обычной (не AJAX) форме — обычно истёкшая сессия.
        // Вместо страницы 419 отправляем на логин и после входа возвращаем назад.
        // Laravel заранее оборачивает TokenMismatchException в HttpException(419)
        $this->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || ! $e->getPrevious() instanceof TokenMismatchException) {
                return null;
            }

            if ($request->expectsJson()) {
                return null; // AJAX обрабатывается на фронте (main.js)
            }

            if ($request->routeIs('logout')) {
                return redirect()->route('login');
            }

            $back = url()->previous();
            $base = rtrim(url('/'), '/');
            $isLocal = $back === $base || str_starts_with($back, $base . '/');

            if (auth()->check()) {
                return redirect()->to($isLocal ? $back : route('cabinet.index'));
            }

            if ($isLocal && $back !== route('login')) {
                redirect()->setIntendedUrl($back);
            }

            return redirect()->route('login');
        });
    }
}
