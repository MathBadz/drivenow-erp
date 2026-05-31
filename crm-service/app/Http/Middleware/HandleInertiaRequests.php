<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private readonly SettingsService $settings) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'settings' => fn () => $this->settings->all(),
            'flash' => [
                'toast' => fn () => $this->resolveToast($request),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array{type: string, message: string}|null
     */
    private function resolveToast(Request $request): ?array
    {
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            if ($request->session()->has($type)) {
                return ['type' => $type, 'message' => (string) $request->session()->get($type)];
            }
        }

        return null;
    }
}
