<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\User\UserExportService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly UserExportService $export,
    ) {}

    public function index(): View
    {
        return view('admin.users.index', [
            'users' => $this->users->paginateCustomers(),
        ]);
    }

    public function show(User $user): View
    {
        $model = $this->users->find($user->id);

        abort_if(! $model, 404);

        return view('admin.users.show', ['user' => $model]);
    }

    public function export(): Response
    {
        return $this->export->downloadExcel();
    }
}
