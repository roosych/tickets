<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TicketsReportExport implements FromCollection, WithHeadings
{
    protected Collection $tickets;

    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    public function collection(): Collection
    {
        return $this->tickets->map(function ($ticket) {
            return [
                'ID' => $ticket->id,
                'Создатель' => $ticket->creator?->name ?? '',
                'Исполнитель' => $ticket->performer?->name ?? '',
                'Отдел' => $ticket->creator?->getDepartment()?->name ?? '',
                'Статус' => $ticket->status?->label() ?? '',
                'Приоритет' => $ticket->priority?->getNameByLocale() ?? '',
                'Дата создания' => $ticket->created_at?->format('d.m.Y H:i') ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Создатель',
            'Исполнитель',
            'Отдел',
            'Статус',
            'Приоритет',
            'Дата создания',
        ];
    }
}
