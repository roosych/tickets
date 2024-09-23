<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageCookieMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang_id = $request->cookie('language');

        if (is_null($lang_id)) {
            return $next($request);
        }

        if (app()->isLocale($lang_id)) {
            return $next($request);
        }

        $language = Language::findActive($lang_id);

        //если такой есть в базе и он активный - устанавливаем в куку
        $language && app()->setLocale($lang_id);

        return $next($request);
    }
}
