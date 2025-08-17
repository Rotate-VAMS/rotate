@component('mail::message')
# 🎉 Welcome to {{ $tenant->name }} - Your Plan is Now Active!

Dear {{ $tenant->name }} Administrator,

Congratulations! Your **{{ config("plans.{$tenant->plan_key}.name") }}** plan has been successfully activated and your virtual airline management system is now ready to use.

## 📋 Plan Details

**Plan:** {{ config("plans.{$tenant->plan_key}.name") }}  
**Amount:** ${{ config("plans.{$tenant->plan_key}.amount") }}{{ config("plans.{$tenant->plan_key}.interval") ? '/' . config("plans.{$tenant->plan_key}.interval") : '' }}  
**Valid Until:** {{ $tenant->plan_valid_until->format('F j, Y') }}  
**Domain:** {{ $tenant->domain }}

## ✨ Your Plan Features

@php
$features = config("plans.{$tenant->plan_key}.display_features", []);
@endphp

@foreach($features as $feature)
✅ {{ $feature }}
@endforeach

## 🚀 Getting Started

Your virtual airline management system is now accessible at your custom domain. Here's what you can do next:

1. **Access Your Dashboard** - Use the button below to log in to your system
2. **Configure Your Airline** - Set up your airline details, fleet, and routes
3. **Invite Pilots** - Start building your virtual airline community
4. **Customize Branding** - Upload your logo and customize the appearance

@component('mail::button', ['url' => $loginUrl, 'color' => 'primary'])
Login to Your Dashboard
@endcomponent

## 🔧 System Access

**Login URL:** [{{ $loginUrl }}]({{ $loginUrl }})  
**Admin Email:** {{ $tenant->admin_email }}  
**Admin Callsign:** {{ $tenant->admin_callsign }}

## 📞 Need Help?

If you have any questions or need assistance setting up your virtual airline:

- **Documentation:** Check our comprehensive guides
- **Support:** Contact our support team for technical assistance
- **Community:** Join our Discord server for tips and community support

## 🔄 Plan Management

- **Upgrade/Downgrade:** You can modify your plan anytime from your dashboard
- **Billing:** Manage your subscription and payment methods
- **Renewal:** Your plan will automatically renew unless cancelled

---

**Thank you for choosing Rotate-VAMS!** We're excited to see your virtual airline take flight.

Best regards,  
The Rotate-VAMS Team

---

*This email was sent to {{ $tenant->admin_email }}. If you have any questions about your account, please contact our support team.*
@endcomponent
