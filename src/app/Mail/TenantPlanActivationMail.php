<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;

class TenantPlanActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->loginUrl = $tenant->getBaseUrl() . '/login';
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Your ' . $this->tenant->name . ' Rotate-VAMS plan is active now!')
                    ->markdown('emails.tenant-plan-activation', [
                        'tenant' => $this->tenant,
                        'loginUrl' => $this->loginUrl,
                    ]);
    }
} 