@extends('layouts.admin')

@section('title', 'Edit category')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Edit category</h1>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label class="text-xs font-semibold text-slate-600">Parent</label>
            <select name="parent_id" class="mt-1 w-full rounded-md border-slate-200 text-sm">
                <option value="">— Root category —</option>
                @foreach ($parentOptions as $opt)
                    <option value="{{ $opt['id'] }}" @selected(old('parent_id', $category->parent_id) == $opt['id'])>{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Name</label>
            <input name="name" value="{{ old('name', $category->name) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Slug</label>
            <input name="slug" value="{{ old('slug', $category->slug) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-600">Max products</label>
            <input type="number" name="max_products" value="{{ old('max_products', $category->max_products) }}" class="mt-1 w-full rounded-md border-slate-200 text-sm" required>
        </div>
        @if ($category->menu_image_path)
            <div class="flex items-center gap-3">
                <img src="{{ $category->menuImageUrl() }}" alt="" class="h-16 w-16 rounded object-cover ring ring-slate-100">
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remove_menu_image" value="1" class="rounded border-slate-300">
                    Remove image
                </label>
            </div>
        @endif
        <div>
            <label class="text-xs font-semibold text-slate-600">New menu image</label>
            <input type="file" name="menu_image" accept="image/*" class="mt-1 w-full text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-brand-600" @checked(old('is_active', $category->is_active))>
            Active
        </label>
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update</button>
    </form>
@endsection
