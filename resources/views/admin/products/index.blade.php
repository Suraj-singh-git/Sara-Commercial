@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-zinc-600">Manage catalog listings, variants, and visibility.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-zinc-950 hover:bg-brand-500">New product</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-zinc-200 text-sm">
            <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-600">
                <tr>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Variants</th>
                    <th class="px-4 py-3">Visible</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @foreach ($products as $product)
                    <tr>
                        @php $thumb = $product->images->first(); @endphp
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($thumb)
                                    <img src="{{ $thumb->url() }}" alt="" class="h-12 w-12 rounded-lg object-cover ring-1 ring-zinc-200" width="48" height="48">
                                @else
                                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-zinc-100 text-[11px] font-medium text-zinc-500">No</span>
                                @endif
                                <div>
                                    <p class="font-semibold text-zinc-900">{{ $product->name }}</p>
                                    <p class="text-xs text-zinc-500">ID #{{ $product->id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-700">{{ $product->category->name }}</td>
                        <td class="px-4 py-3 font-semibold text-zinc-800">{{ $product->variants->count() }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_visible ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-600' }}">
                                {{ $product->is_visible ? 'Visible' : 'Hidden' }}
                            </span>
                        </td>
                        <td class="space-x-2 px-4 py-3 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete product?')">
                                @csrf
                                @method('DELETE')
                                <button class="font-semibold text-rose-600 hover:text-rose-700" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-zinc-100 px-4 py-3">{{ $products->links() }}</div>
    </div>
@endsection
