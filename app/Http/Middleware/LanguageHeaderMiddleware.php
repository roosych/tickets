<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageHeaderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $languages = Language::getActive();

        //$language = $request->header('Accept-Language');
        $language = $request->getPreferredLanguage(
            $languages->pluck('id')->toArray(), //en_US, ru_RU
        );

        /*
         * Выполняется после апп сервис провайдера, где устанавливается язык по умолчанию методом сетдефолт
         * и если есть язык в базе, то мдлвейр переопределит его
         */
        $language && app()->setLocale($language);

        return $next($request);
    }
}
