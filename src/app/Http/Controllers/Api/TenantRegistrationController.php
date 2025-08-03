<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TenantRegistrationMail;

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
        
        // Send welcome email
        $this->sendWelcomeEmail($tenant, $data['admin_email'], $data['admin_password']);

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

    /**
     * Send welcome email to the admin user after successful registration
     */
    private function sendWelcomeEmail(Tenant $tenant, string $adminEmail, string $adminPassword): void
    {
        $adminUser = User::where('tenant_id', $tenant->id)
                        ->where('email', $adminEmail)
                        ->first();

        if ($adminUser) {
            try {
                Mail::to($adminUser->email)->send(new TenantRegistrationMail($tenant, $adminUser, $adminPassword));
            } catch (\Exception $e) {
                Log::error('Failed to send welcome email: ' . $e->getMessage());
            }
        }
    }
}