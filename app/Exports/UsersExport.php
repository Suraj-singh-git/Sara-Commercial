<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn ($u) => [
            'name' => $u->name,
            'phone' => $u->phone,
            'email' => $u->email,
            'orders_count' => $u->orders_count,
        ]);
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Email', 'Orders count'];
    }
}
