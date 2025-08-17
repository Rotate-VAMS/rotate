<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;

class TenantPlanExpireMail extends Mailable
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
        return $this->subject('Your ' . $this->tenant->name . ' Rotate-VAMS plan is expired now!')
                    ->markdown('emails.tenant-plan-expire', [
                        'tenant' => $this->tenant,
                        'loginUrl' => $this->loginUrl,
                    ]);
    }
} 