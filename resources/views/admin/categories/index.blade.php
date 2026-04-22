@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Categories</h1>
        <a href="{{ route('admin.categories.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">New category</a>
    </div>
    <p class="mt-2 max-w-2xl text-sm text-slate-600">Build up to 3 levels: main → sub → sub-sub. These drive the storefront side menu. Upload a square menu image for best results.</p>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-600">
                <tr>
                    <th class="px-4 py-3 w-14"></th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Parent</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Sort</th>
                    <th class="px-4 py-3">Max</th>
                    <th class="px-4 py-3">Active</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($categories as $category)
                    <tr>
                        <td class="px-4 py-2">
                            @if ($category->menu_image_path)
                                <img src="{{ $category->menuImageUrl() }}" alt="" class="h-10 w-10 rounded object-cover ring-1 ring-slate-200">
                            @else
                                <span class="block h-10 w-10 rounded bg-slate-100"></span>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-semibold text-slate-900">{{ $category->name }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $category->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $category->slug }}</td>
                        <td class="px-4 py-2">{{ $category->sort_order }}</td>
                        <td class="px-4 py-2">{{ $category->max_products }}</td>
                        <td class="px-4 py-2">{{ $category->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-brand-700 hover:text-brand-800">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-rose-600 hover:text-rose-700" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-4 py-3">{{ $categories->links() }}</div>
    </div>
@endsection
