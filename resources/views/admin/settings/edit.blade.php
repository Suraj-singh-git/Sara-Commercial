@extends('layouts.admin')

@section('title', 'Website Settings')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-lg font-semibold text-zinc-900">Brand settings</h2>
            <p class="mt-1 text-sm text-zinc-600">Update logo, title, company name, and theme color used across the storefront and account/admin headers.</p>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-xs font-semibold text-zinc-600">Site title</label>
                    <input name="site_title" value="{{ old('site_title', $setting->site_title) }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm" required>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-600">Company name</label>
                    <input name="company_name" value="{{ old('company_name', $setting->company_name) }}" class="mt-1 w-full rounded-lg border-zinc-300 text-sm shadow-sm" required>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-600">Theme color</label>
                    <div class="mt-1 flex items-center gap-3">
                        <input type="color" id="theme_color_picker" value="{{ old('theme_color', $setting->theme_color) }}" class="h-10 w-16 rounded border border-zinc-300 p-1">
                        <input id="theme_color_input" name="theme_color" value="{{ old('theme_color', $setting->theme_color) }}" class="w-40 rounded-lg border-zinc-300 text-sm shadow-sm" pattern="^#[0-9A-Fa-f]{6}$" required>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-zinc-600">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="mt-1 w-full text-sm">
                    @if ($setting->logo_path)
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-zinc-700">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-zinc-300">
                            Remove current logo
                        </label>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Save settings</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-500">Preview</h3>
                <div class="mt-4 rounded-lg border border-zinc-200 p-4">
                    <div class="flex items-center gap-3">
                        @if ($setting->logoUrl())
                            <img src="{{ $setting->logoUrl() }}" alt="" class="h-10 w-10 rounded object-cover">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black text-white" style="background-color: {{ $setting->theme_color }}">S</span>
                        @endif
                        <div>
                            <p class="text-sm font-bold text-zinc-900">{{ $setting->company_name }}</p>
                            <p class="text-xs text-zinc-500">{{ $setting->site_title }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-zinc-900">Reset defaults</h3>
                <p class="mt-1 text-sm text-zinc-600">Reset to current default setup (Sara Commercial, default title, and emerald theme color).</p>
                <form method="POST" action="{{ route('admin.settings.reset') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50" onclick="return confirm('Reset website settings to default values?')">Reset to default</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        (() => {
            const picker = document.getElementById('theme_color_picker');
            const input = document.getElementById('theme_color_input');
            if (!picker || !input) return;

            picker.addEventListener('input', () => {
                input.value = picker.value;
            });
            input.addEventListener('input', () => {
                if (/^#[0-9A-Fa-f]{6}$/.test(input.value)) {
                    picker.value = input.value;
                }
            });
        })();
    </script>
@endsection

