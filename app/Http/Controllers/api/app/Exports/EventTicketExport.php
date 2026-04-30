<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class EventTicketExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'Booking No',
            'Booking Date',
            'Booking Time',
            'Member',
            'Ticket Name',
            'Participant Name',
            'Entry Status',
            'Food Status',
            'Amount',
            'Payment Status',
            'Payment Type',
            'Payment Ref No',
            'Razorpay Order ID'
        ];
    }
}