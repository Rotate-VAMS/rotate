<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Services\TenantStorageService;

// Import helper functions
use function App\Helpers\tenant_storage_url;
use function App\Helpers\tenant_asset_url;
use function App\Helpers\tenant_url;

class TestTenantStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tenant-storage {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant-specific storage URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        
        if ($domain) {
            $tenant = Tenant::where('domain', $domain)->first();
            if (!$tenant) {
                $this->error("Tenant with domain '{$domain}' not found.");
                return 1;
            }
            
            $this->testTenant($tenant);
        } else {
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $this->testTenant($tenant);
                $this->line('');
            }
        }
        
        return 0;
    }
    
    private function testTenant(Tenant $tenant)
    {
        $this->info("Testing tenant: {$tenant->name} ({$tenant->domain})");
        
        // Simulate the tenant being the current tenant
        app()->instance('currentTenant', $tenant);
        
        $baseUrl = TenantStorageService::getTenantBaseUrl();
        $storageUrl = TenantStorageService::getStorageUrl('test/file.jpg');
        
        $this->line("Base URL: {$baseUrl}");
        $this->line("Storage URL: {$storageUrl}");
        
        // Test the helper functions
        $this->line("Helper - tenant_storage_url: " . tenant_storage_url('test/file.jpg'));
        $this->line("Helper - tenant_asset_url: " . tenant_asset_url('images/logo.png'));
        $this->line("Helper - tenant_url: " . tenant_url('dashboard'));
    }
} 