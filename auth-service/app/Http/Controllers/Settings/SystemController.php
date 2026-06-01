<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SystemSettingsUpdateRequest;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
        $validated = $request->validated();

        // Text settings only — logo is handled as a file below.
        $data = collect($validated)->except(['logo', 'remove_logo'])->all();

        if ($request->hasFile('logo')) {
            $this->deleteCurrentLogo();
            $data['logo_url'] = $this->storeLogo($request);
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteCurrentLogo();
            $data['logo_url'] = '';
        }

        Setting::setMany($data);

        ActivityLog::record(
            'settings.updated',
            'Global system settings were updated',
            'warning',
            $request->user()?->name,
        );

        return back()->with('success', 'System settings updated successfully.');
    }

    /**
     * Store the uploaded logo in public/logos and return its absolute URL
     * (consumed by every other service via GET /api/v1/settings). The URL is
     * built from APP_URL, which must point at this service's public host —
     * set it to the gateway/public origin when deployed behind C2's gateway.
     */
    private function storeLogo(SystemSettingsUpdateRequest $request): string
    {
        $dir = public_path('logos');
        File::ensureDirectoryExists($dir);

        $file = $request->file('logo');
        // Random suffix avoids collisions when two logos are saved in the same
        // second (a YmdHis-only name would overwrite the earlier upload).
        $name = 'logo-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($dir, $name);

        return rtrim((string) config('app.url'), '/').'/logos/'.$name;
    }

    /**
     * Delete the currently-stored logo file (if any) so replaced/removed logos
     * do not accumulate as orphans in public/logos.
     */
    private function deleteCurrentLogo(): void
    {
        $current = (string) Setting::value('logo_url', '');

        if ($current === '') {
            return;
        }

        $path = public_path('logos/'.basename(parse_url($current, PHP_URL_PATH) ?: ''));

        if (basename($path) !== '' && File::exists($path)) {
            File::delete($path);
        }
    }
}
