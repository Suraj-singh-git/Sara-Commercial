@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Customers</h1>
        <a href="{{ route('admin.users.export') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-white">Export Excel</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-600">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Orders</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $user->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->orders_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-brand-700 hover:text-brand-800">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-4 py-3">{{ $users->links() }}</div>
    </div>
@endsection
