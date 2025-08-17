@component('mail::message')
# ⚠️ Your {{ $tenant->name }} Plan Has Expired

Dear {{ $tenant->name }} Administrator,

Your **{{ config("plans.{$tenant->plan_key}.name") }}** plan has expired on **{{ $tenant->plan_valid_until->format('F j, Y') }}**.

## 📋 Expired Plan Details

**Plan:** {{ config("plans.{$tenant->plan_key}.name") }}  
**Amount:** ${{ config("plans.{$tenant->plan_key}.amount") }}{{ config("plans.{$tenant->plan_key}.interval") ? '/' . config("plans.{$tenant->plan_key}.interval") : '' }}  
**Expired On:** {{ $tenant->plan_valid_until->format('F j, Y') }}  
**Domain:** {{ $tenant->domain }}

## 🔒 What This Means

Your virtual airline management system is currently in a restricted state. You may experience:

- Limited access to premium features
- Reduced functionality for advanced operations
- Potential data access restrictions

## 🚀 Renew Your Plan

To restore full functionality and continue enjoying all features, please renew your subscription:

@component('mail::button', ['url' => 'https://rotate-vams.com/checkout?tenant_id=' . $tenant->id, 'color' => 'primary'])
Renew Your Plan
@endcomponent

## 💡 Plan Options

You can choose to:

1. **Renew Current Plan** - Continue with your existing {{ config("plans.{$tenant->plan_key}.name") }} plan
2. **Upgrade Plan** - Get more features and capabilities
3. **Downgrade Plan** - Switch to a more affordable option
4. **Switch to Free Plan** - Basic functionality with limited features

## ✨ Your Previous Plan Features

@php
$features = config("plans.{$tenant->plan_key}.display_features", []);
@endphp

@foreach($features as $feature)
✅ {{ $feature }}
@endforeach

## 🔧 Access Your Account

**Login URL:** [{{ $loginUrl }}]({{ $loginUrl }})  
**Admin Email:** {{ $tenant->admin_email }}  
**Admin Callsign:** {{ $tenant->admin_callsign }}

## 📞 Need Assistance?

If you have any questions about your plan or need help with renewal:

- **Billing Support:** Contact our billing team for payment issues
- **Technical Support:** Get help with account access and features
- **Plan Consultation:** Discuss the best plan for your virtual airline needs

## ⏰ Important Notes

- **Data Safety:** Your airline data is safe and will be restored once you renew
- **Grace Period:** You have a grace period to renew without losing any data
- **Automatic Renewal:** Consider enabling automatic renewal to avoid future interruptions

---

**We value your business and want to help you get back to managing your virtual airline!**

Best regards,  
The Rotate-VAMS Team

---

*This email was sent to {{ $tenant->admin_email }}. If you have any questions about your account, please contact our support team.*
@endcomponent
