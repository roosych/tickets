<?php

namespace App\Http\Filters;

use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TicketFilter extends QueryBuilder
{
    public static function filter(): array
    {
        return [
            AllowedFilter::exact('executor_id'),
            AllowedFilter::exact('priorities_id'),
            AllowedFilter::exact('department_id'),
            AllowedFilter::scope('date_range', 'filterByDateRange'),
        ];
    }
}
