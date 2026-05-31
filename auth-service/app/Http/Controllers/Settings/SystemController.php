<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SystemSettingsUpdateRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    /**
     * Show the global system settings form.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/System', [
            'settings' => Setting::publicPayload(),
        ]);
    }

    /**
     * Persist the global system settings (admin only).
     */
    public function update(SystemSettingsUpdateRequest $request): RedirectResponse
    {
        Setting::setMany($request->validated());

        return back()->with('success', 'System settings updated successfully.');
    }
}
