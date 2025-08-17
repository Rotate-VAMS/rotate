<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn () => $request->user()
                    ? array_merge(
                        $request->user()->only(['id', 'name', 'email']),
                        [
                            'role' => $request->user()->getRoleNames()->first(),
                            'rank' => $request->user()->rank?->name ?? null,
                            'permissions' => $request->user()->getPermissionsViaRoles()->pluck('name'),
                            'status' => $request->user()->status,
                            'flying_hours' => $request->user()->flying_hours,
                            'created_at' => $request->user()->created_at->format('M Y'),
                            'discord_id' => $request->user()->discord_id,
                        ]
                    )
                    : null,
                'tenant' => fn () => app('currentTenant'),
                'plan' => fn () => config('plans.' . app('currentTenant')->plan_key) ?? null,
                'version' => fn () => config('app.version'),
            ],
            'csrf_token' => fn () => csrf_token(),
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }
}
