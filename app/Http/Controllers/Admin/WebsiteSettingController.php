<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WebsiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'setting' => WebsiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = WebsiteSetting::current();

        $data = $request->validate([
            'site_title' => ['required', 'string', 'max:180'],
            'company_name' => ['required', 'string', 'max:120'],
            'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_logo') && $setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('settings/logo', 'public');
        }

        unset($data['logo'], $data['remove_logo']);

        $setting->update($data);

        return redirect()->route('admin.settings.edit')->with('status', 'Website settings updated.');
    }

    public function reset(): RedirectResponse
    {
        $setting = WebsiteSetting::current();
        if ($setting->logo_path) {
            Storage::disk('public')->delete($setting->logo_path);
        }

        $setting->update(WebsiteSetting::defaults());

        return redirect()->route('admin.settings.edit')->with('status', 'Website settings reset to defaults.');
    }
}

