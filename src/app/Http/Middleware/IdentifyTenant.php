<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;
use function App\Helpers\set_permissions_team_id;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost(); // e.g., va1.test
        $tenant = Tenant::where('domain', $host)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        app()->instance('currentTenant', $tenant);

        // Set the permissions team ID for tenant-aware roles and permissions
        set_permissions_team_id($tenant->id);

        return $next($request);
    }
}
