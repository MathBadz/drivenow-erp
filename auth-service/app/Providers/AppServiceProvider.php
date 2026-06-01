<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Services\JwtService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->recordAuthActivity();
        $this->issueCrossServiceToken();
    }

    /**
     * Log staff sign-in / sign-out to the system activity feed.
     */
    protected function recordAuthActivity(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            $name = $event->user->name ?? 'A user';
            ActivityLog::record('auth.login', "{$name} signed in", 'success', $name);

            // Welcome toast — shown on the dashboard right after sign-in.
            session()->flash('success', 'Welcome back, '.(strtok((string) $name, ' ') ?: 'there').'!');
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $name = $event->user->name ?? 'A user';
            ActivityLog::record('auth.logout', "{$name} signed out", 'info', $name);
        });
    }

    /**
     * Issue (on login) and revoke (on logout) the signed JWT cookie that
     * powers stateless single sign-on across every other service. The cookie
     * carries identity only — no service shares the auth database.
     */
    protected function issueCrossServiceToken(): void
    {
        $minutes = (int) config('session.lifetime', 120);

        Event::listen(Login::class, function (Login $event) use ($minutes): void {
            $user = $event->user;
            $token = app(JwtService::class)->issue([
                'sub' => $user->getAuthIdentifier(),
                'name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'role' => $user->role?->value ?? (string) ($user->role ?? ''),
                'role_label' => method_exists($user, 'roleLabel') ? $user->roleLabel() : '',
            ], $minutes * 60);

            // Unencrypted, port-agnostic cookie so every localhost service can
            // read the raw JWT (see encryptCookies "except" list).
            Cookie::queue(Cookie::make('drivenow_token', $token, $minutes, '/', null, false, true, false, 'lax'));
        });

        Event::listen(Logout::class, function (): void {
            Cookie::queue(Cookie::forget('drivenow_token', '/'));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        // Force all generated URLs (redirects, route(), url(), asset()) to use
        // APP_URL as the root. Without this, Docker's nginx (SERVER_PORT=80)
        // causes Laravel to generate http://localhost/... instead of :8001/...
        URL::forceRootUrl(config('app.url'));

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
