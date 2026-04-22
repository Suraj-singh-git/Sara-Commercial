<?php

namespace App\Services\User;

use App\Exports\UsersExport;
use App\Repositories\Contracts\UserRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserExportService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function downloadExcel(): BinaryFileResponse
    {
        return Excel::download(new UsersExport($this->users->exportRows()), 'users.xlsx');
    }
}
