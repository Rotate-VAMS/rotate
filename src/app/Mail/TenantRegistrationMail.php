<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use App\Models\User;

class TenantRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public User $adminUser;
    public string $adminPassword;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, User $adminUser, string $adminPassword)
    {
        $this->tenant = $tenant;
        $this->adminUser = $adminUser;
        $this->adminPassword = $adminPassword;
        $this->loginUrl = $tenant->getBaseUrl() . '/login';
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Welcome to ' . $this->tenant->name . ' - Your Account is Ready!')
                    ->markdown('emails.tenant-registration', [
                        'tenant' => $this->tenant,
                        'adminUser' => $this->adminUser,
                        'adminPassword' => $this->adminPassword,
                        'loginUrl' => $this->loginUrl,
                    ]);
    }
} 