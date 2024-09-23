<?php
if (! function_exists('active_link')) {
    function active_link(array $route, string $active = 'here show'): string
    {
        return request()->routeIs($route) ? $active : '';
    }
}

if (! function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return implode('/', array_filter([
            trim(config('app.url'), '/'),
            trim($path, '/'),
        ]));
    }
}

if (! function_exists('bytes_to_mb')) {
    function bytes_to_mb(int $bytes): string
    {
        $megabytes = $bytes / 1024 / 1024;
        return number_format($megabytes, 2) . ' MB';
    }
}

if (!function_exists('get_initials')) {
    function get_initials(string $fullName): string
    {
        return collect(explode(' ', $fullName))
            ->map(fn($word) => mb_substr($word, 0, 1))
            ->implode('');
    }
}

