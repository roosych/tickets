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
if (!function_exists('generateDepartmentCode')) {
    /**
     * Generate department code in format #ABC-123 using ticket ID
     *
     * @param string $departmentName
     * @param int $ticketId
     * @return string
     */
    function generateDepartmentCode($departmentName, $ticketId): string
    {
        // Convert to uppercase and remove any special characters
        $cleaned = preg_replace('/[^A-Za-z]/', '', strtoupper($departmentName));

        // Get first 3 letters, if less than 3 letters pad with 'X'
        $abbreviation = substr($cleaned . 'XXX', 0, 3);

        // Format ticket ID to ensure it's 3 digits with leading zeros
        $formattedTicketId = str_pad($ticketId, 3, '0', STR_PAD_LEFT);

        return "#{$abbreviation}-{$formattedTicketId}";
    }
}
