@extends('layouts.admin')

@section('title', 'Edit product')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Edit product</h1>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-6 space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-semibold text-slate-600">Category (any level)</label>
                <select name="category_id" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
                    @foreach ($categoryOptions as $opt)
                        <option value="{{ $opt['id'] }}" @selected($product->category_id === $opt['id'])>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">Slug</label>
                <input name="slug" value="{{ old('slug', $product->slug) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm">
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Name</label>
            <input name="name" value="{{ old('name', $product->name) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Short description</label>
            <input name="short_description" value="{{ old('short_description', $product->short_description) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Detailed description</label>
            <textarea name="detailed_description" rows="5" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>{{ old('detailed_description', $product->detailed_description) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_visible" value="1" class="rounded border-slate-300 text-brand-600" @checked(old('is_visible', $product->is_visible))>
            Visible on storefront
        </label>

        <div>
            <label class="text-xs font-semibold text-slate-600">Add images</label>
            <input type="file" name="images[]" multiple class="mt-1 w-full text-sm">
        </div>

        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-slate-900">Variants</h2>
            @foreach ($product->variants as $index => $variant)
                <div class="grid gap-3 rounded-lg border border-slate-100 p-3 md:grid-cols-4">
                    <input name="variants[{{ $index }}][type_key]" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$index.'.type_key', $variant->type_key) }}" required>
                    <input name="variants[{{ $index }}][type_value]" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$index.'.type_value', $variant->type_value) }}" required>
                    <input name="variants[{{ $index }}][price]" type="number" step="0.01" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$index.'.price', $variant->price) }}" required>
                    <input name="variants[{{ $index }}][stock]" type="number" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$index.'.stock', $variant->stock) }}">
                </div>
            @endforeach
        </div>

        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update product</button>
    </form>
@endsection
