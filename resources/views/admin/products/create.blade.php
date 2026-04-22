@extends('layouts.admin')

@section('title', 'New product')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Create product</h1>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-semibold text-slate-600">Category (any level)</label>
                <select name="category_id" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
                    @foreach ($categoryOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600">Slug (optional)</label>
                <input name="slug" class="mt-1 w-full rounded-md border-slate-200 text-sm">
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Name</label>
            <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Short description</label>
            <input name="short_description" value="{{ old('short_description') }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Detailed description</label>
            <textarea name="detailed_description" rows="5" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>{{ old('detailed_description') }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_visible" value="1" class="rounded border-slate-300 text-brand-600" checked>
            Visible on storefront
        </label>

        <div>
            <label class="text-xs font-semibold text-slate-600">Images</label>
            <input type="file" name="images[]" multiple class="mt-1 w-full text-sm">
        </div>

        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-slate-900">Variants (1-6)</h2>
            @for ($i = 0; $i < 4; $i++)
                <div class="grid gap-3 rounded-lg border border-slate-100 p-3 md:grid-cols-4">
                    <input name="variants[{{ $i }}][type_key]" placeholder="Type" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$i.'.type_key', ['Capacity','Color','Warranty','Bundle'][$i] ?? '') }}">
                    <input name="variants[{{ $i }}][type_value]" placeholder="Value" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$i.'.type_value', ['128GB','Graphite','1 Year','Standard'][$i] ?? '') }}">
                    <input name="variants[{{ $i }}][price]" type="number" step="0.01" placeholder="Price" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$i.'.price', 1000 + $i * 250) }}">
                    <input name="variants[{{ $i }}][stock]" type="number" placeholder="Stock" class="rounded-md border-slate-200 text-sm" value="{{ old('variants.'.$i.'.stock', 20) }}">
                </div>
            @endfor
        </div>

        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save product</button>
    </form>
@endsection
