<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['addresses' => fn ($q) => $q->where('is_default', true)]);

        return view('profile.edit', [
            'user' => $user,
            'defaultAddress' => $user->addresses->first(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill(collect($request->validated())->only(['name', 'email', 'phone'])->all());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $data = $request->validated();

        if (! empty($data['address_line1']) && ! empty($data['address_city']) && ! empty($data['address_postal_code'])) {
            Address::query()->updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'is_default' => true,
                ],
                [
                    'label' => 'Default',
                    'line1' => $data['address_line1'],
                    'line2' => $data['address_line2'] ?? null,
                    'city' => $data['address_city'],
                    'state' => $data['address_state'] ?? null,
                    'postal_code' => $data['address_postal_code'],
                    'country' => $data['address_country'] ?? 'IN',
                ],
            );
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
