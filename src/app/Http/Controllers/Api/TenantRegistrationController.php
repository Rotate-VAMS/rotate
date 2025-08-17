<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Notifications;

class TenantRegistrationController extends Controller
{
    public function preRegister(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/i',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'admin_callsign' => 'required|string|max:255',
        ]);
 
        // Register the tenant
        Artisan::call('tenant:register', [
            'name' => $data['name'],
            'domain' => $data['domain'],
            '--admin-email' => $data['admin_email'],
            '--admin-password' => $data['admin_password'],
            '--admin-callsign' => $data['admin_callsign'],
        ]);

        // Fetch the new tenant
        $tenant = Tenant::where('domain', $data['domain'])->first();

        // Create a new notification
        $notification = Notifications::createNotification($tenant->id, Notifications::NOTIFICATION_TYPE_NEW_REGISTRATION);
        if (!$notification) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Failed to create notification.',
            ], 500);
        }
        
        return response()->json([
            'status' => 'success', 
            'message' => 'Tenant registered successfully.',
            'tenant_id' => $tenant->id,
        ]);
    }

    public function checkDomainAvailability($domain)
    {
        $exists = Tenant::where('domain', $domain)->exists();
        return response()->json(['status' => 'success', 'available' => !$exists]);
    }

    public function fetchDomain($tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Tenant not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Tenant fetched successfully.',
            'domain' => $tenant->domain,
        ]);
    }
}