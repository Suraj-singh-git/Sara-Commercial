@extends('layouts.admin')

@section('title', 'New category')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Create category</h1>

    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="text-xs font-semibold text-slate-600">Parent (leave empty for top-level)</label>
            <select name="parent_id" class="mt-1 w-full rounded-md border-slate-200 text-sm">
                <option value="">— Root category —</option>
                @foreach ($parentOptions as $opt)
                    <option value="{{ $opt['id'] }}" @selected(old('parent_id') == $opt['id'])>{{ $opt['label'] }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Name</label>
            <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Slug (optional)</label>
            <input name="slug" value="{{ old('slug') }}" class="mt-1 w-full rounded-md border-slate-200 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Max products (leaf categories)</label>
            <input type="number" name="max_products" value="{{ old('max_products', 25) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Menu image (shown in side nav)</label>
            <input type="file" name="menu_image" accept="image/*" class="mt-1 w-full text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-brand-600" @checked(old('is_active', true))>
            Active
        </label>
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save</button>
    </form>
@endsection
