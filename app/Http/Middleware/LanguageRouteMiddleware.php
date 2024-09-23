<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageRouteMiddleware
{
    private string|null $prefix;
    private string $currentLanguage;
    private string $defaultLanguage;

    public function __construct()
    {
        $this->prefix = Language::routePrefix();
        $this->currentLanguage = app()->getLocale();
        $this->defaultLanguage = Language::findDefault()->id;
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
        //если язык по умолчанию равен текущему языку и префикса нет
//        if ($this->currentLanguage === $this->defaultLanguage) {
//            if (is_null($this->prefix)) {
//                return $next($request);
//            }
//        }
//
//        if ($this->prefix === $this->currentLanguage) {
//            return $next($request);
//        }
//
//
//
//        return $this->redirect($request);

    }

    private function redirect(Request $request): RedirectResponse
    {
        $url = $this->currentLanguage;

        //если язык равен языку по умолчанию то префикс добавлять не будем
        if ($this->currentLanguage === $this->defaultLanguage) {
            $url = '';
        }

        $segments = $request->segments();

        //если есть префикс то меняем(убираем из массива сегментс), если нет то добавляем ($url = $currentLanguage;)
        $this->prefix && array_shift($segments);

        //если есть сегменты в $path то склеиваем их с урлом слешем
        if ($path = implode('/', $segments)) {
            $url .= "/{$path}";
        }
        //если есть query то склеиваем их с урлом
        if ($query = $request->getQueryString()) {
            $url .= "?{$query}";
        }

        return redirect($url);
    }
}
