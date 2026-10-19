<?php

use App\Enums\TicketStatusEnum;
use Illuminate\Support\Carbon;

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

if (!function_exists('pluralize')) {
    function pluralize($number, $words): string
    {
        $forms = explode('|', $words);
        $form = $number % 10 === 1 && $number % 100 !== 11 ? $forms[0]
            : ($number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 12 || $number % 100 > 14) ? $forms[1] : $forms[2]);

        return "{$number} {$form}";
    }
}

if (!function_exists('formatTimeDifference')) {
    function formatTimeDifference($difference): string
    {
        $timeString = '';
        // Берём общее число дней, чтобы не терять месяцы и годы
        $days = $difference->days !== false ? $difference->days : $difference->d;
        // Добавляем дни, если они больше 0
        if ($days > 0) {
            $timeString .= pluralize($days, 'д|д|д') . ' ';
        }
        // Добавляем часы, если они больше 0
        if ($difference->h > 0) {
            $timeString .= pluralize($difference->h, 'ч|ч|ч') . ' ';
        }
        // Добавляем минуты
        $timeString .= pluralize($difference->i, 'м|м|м');
        return trim($timeString); // Убираем лишний пробел
    }
}

if (!function_exists('intervalMinutes')) {
    function intervalMinutes(\DateInterval $difference): int
    {
        $days = $difference->days !== false ? $difference->days : $difference->d;

        return ($days * 1440) + ($difference->h * 60) + $difference->i;
    }
}

if (!function_exists('formatMinutes')) {
    function formatMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        return trim(
            ($days ? $days . ' д ' : '')
            . ($hours ? $hours . ' ч ' : '')
            . (($mins || (!$days && !$hours)) ? $mins . ' м' : '')
        );
    }
}

if (!function_exists('calculateTimeDifference')) {
    function calculateTimeDifference($startTime, $endTime): \DateInterval
    {
        return $startTime->diff(Carbon::parse($endTime));
    }
}

if (!function_exists('getStatusChangeTime'))
{
    function getStatusChangeTime ($statuses, TicketStatusEnum $status) {
        return isset($statuses[$status->value])
            ? $statuses[$status->value]->first()->created_at
            : null;
    };
}

