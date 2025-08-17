<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;
use App\Helpers\RotateConstants;
use App\Models\Notifications;
use Illuminate\Support\Facades\Log;

class TenantPlanVerifier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cache tenant plan data for 1 hour (3600 seconds)
        $tenant = tenant_cache_remember('tenant:plan_data', RotateConstants::SECONDS_IN_ONE_HOUR, function () {
            return Tenant::find(app('currentTenant')->id);
        });

        if ($tenant->plan_key == 'free') {
            return $next($request);
        }

        $planValidUntil = strtotime($tenant->plan_valid_until);
        $now = time();

        if ($planValidUntil < $now) {
            // Plan has expired, update tenant and clear cache
            $tenant->plan_valid_until = null;
            $tenant->plan_key = 'free';
            $tenant->save();

            // Create a new notification
            $notification = Notifications::createNotification($tenant->id, Notifications::NOTIFICATION_TYPE_PLAN_EXPIRATION);
            if (!$notification) {
                Log::error('Failed to create expiry notification for tenant ' . $tenant->id . ' at ' . now());
            }

            // Clear the cached plan data since it's now invalid
            tenant_cache_forget('tenant:plan_data');
        }

        return $next($request);
    }
}
