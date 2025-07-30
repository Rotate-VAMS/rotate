<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantRegistrationMail;
use App\Models\Tenant;
use App\Models\User;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {--email=test@example.com : Email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the tenant registration email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        
        $this->info("Testing email functionality...");
        $this->info("Sending test email to: {$email}");

        // Create a mock tenant and user for testing
        $tenant = new Tenant([
            'id' => 32323,
            'name' => 'Testwwww Virtual Airline',
            'domain' => 'testwewew.rotate.com',
        ]);

        $adminUser = new User([
            'id' => 999,
            'name' => 'Test Admin',
            'email' => $email,
            'callsign' => 'TESTADMIN',
        ]);

        $adminPassword = 'testpassword123';

        try {
            Mail::to($email)->send(new TenantRegistrationMail($tenant, $adminUser, $adminPassword));
            $this->info("✅ Test email sent successfully!");
            $this->info("Check your email inbox or mail logs for the test message.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to send test email: " . $e->getMessage());
            $this->error("Exception: " . $e->getTraceAsString());
            
            return 1;
        }
    }
} 