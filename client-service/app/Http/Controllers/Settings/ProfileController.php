<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user->avatar);
            $user->avatar = $this->storeAvatar($request->file('avatar'));
        } elseif ($request->boolean('remove_avatar')) {
            $this->deleteAvatar($user->avatar);
            $user->avatar = null;
        }

        unset($validated['avatar'], $validated['remove_avatar']);
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Store an uploaded avatar under public/avatars and return a root-relative
     * URL (resolves against the portal host in any environment).
     */
    private function storeAvatar(UploadedFile $file): string
    {
        $dir = public_path('avatars');
        File::ensureDirectoryExists($dir);

        $name = 'avatar-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($dir, $name);

        return '/avatars/'.$name;
    }

    /** Remove a previously-uploaded avatar file (only our own local uploads). */
    private function deleteAvatar(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/avatars/')) {
            return;
        }

        $path = public_path(ltrim($url, '/'));

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
