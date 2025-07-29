<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;
use function App\Helpers\get_tenant_cache_key;

class TestTenantCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tenant-cache {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant-aware caching functionality';

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
        
        $testKey = 'test:cache:key';
        $testValue = "Test value for tenant {$tenant->id}";
        
        // Test cache key generation
        $tenantCacheKey = get_tenant_cache_key($testKey);
        $this->line("Generated cache key: {$tenantCacheKey}");
        
        // Test cache remember
        $cachedValue = tenant_cache_remember($testKey, 300, function () use ($testValue) {
            return $testValue;
        });
        
        $this->line("Cached value: {$cachedValue}");
        
        // Verify the value was cached with tenant prefix
        $redisValue = Cache::store('redis')->get($tenantCacheKey);
        $this->line("Redis value: {$redisValue}");
        
        // Test cache forget
        $forgotten = tenant_cache_forget($testKey);
        $this->line("Cache forgotten: " . ($forgotten ? 'true' : 'false'));
        
        // Verify the value was removed
        $redisValueAfter = Cache::store('redis')->get($tenantCacheKey);
        $this->line("Redis value after forget: " . ($redisValueAfter ?: 'null'));
        
        // Test that different tenants have different cache keys
        $this->line("Cache isolation test:");
        $tenant1 = Tenant::first();
        $tenant2 = Tenant::skip(1)->first();
        
        if ($tenant1 && $tenant2) {
            app()->instance('currentTenant', $tenant1);
            $key1 = get_tenant_cache_key('test:isolation');
            tenant_cache_remember('test:isolation', 300, function () {
                return 'tenant1_value';
            });
            
            app()->instance('currentTenant', $tenant2);
            $key2 = get_tenant_cache_key('test:isolation');
            tenant_cache_remember('test:isolation', 300, function () {
                return 'tenant2_value';
            });
            
            $this->line("Tenant 1 key: {$key1}");
            $this->line("Tenant 2 key: {$key2}");
            $this->line("Keys are different: " . ($key1 !== $key2 ? 'true' : 'false'));
            
            // Clean up test data
            app()->instance('currentTenant', $tenant1);
            tenant_cache_forget('test:isolation');
            app()->instance('currentTenant', $tenant2);
            tenant_cache_forget('test:isolation');
        }
    }
} 