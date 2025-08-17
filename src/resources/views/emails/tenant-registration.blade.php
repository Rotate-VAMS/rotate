@component('mail::message')
{{-- Custom Header --}}
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -32px -32px 32px -32px;">
    <div style="max-width: 200px; margin: 0 auto 20px;">
        <div style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 80px; height: 80px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 40px; color: white;">✈️</span>
        </div>
    </div>
    <h1 style="color: white; font-size: 28px; font-weight: 700; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Welcome to {{ $tenant->name }}!</h1>
    <p style="color: rgba(255,255,255,0.9); font-size: 16px; margin: 10px 0 0 0; font-weight: 300;">Your virtual airline is ready for takeoff</p>
</div>

{{-- Success Badge --}}
<div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 15px; margin-bottom: 25px; text-align: center;">
    <span style="color: #155724; font-weight: 600; font-size: 16px;">🎉 Registration Complete - Your Account is Ready!</span>
</div>

<p style="font-size: 16px; line-height: 1.6; color: #4a5568; margin-bottom: 25px;">Your virtual airline has been successfully registered and is ready to use. Your domain is <strong>{{ $tenant->domain }}</strong>. After logging in, you can upgrade your plan from the dashboard to access additional features.</p>

## 🎯 Account Details

<div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; border-radius: 0 6px 6px 0; margin: 20px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Tenant Name:</strong>
            <span style="color: #4a5568;">{{ $tenant->name }}</span>
        </div>
        <div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Domain:</strong>
            <span style="color: #4a5568;">{{ $tenant->domain }}</span>
        </div>
        <div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Admin Email:</strong>
            <span style="color: #4a5568;">{{ $tenant->admin_email }}</span>
        </div>
        <div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Admin Callsign:</strong>
            <span style="color: #4a5568;">{{ $tenant->admin_callsign }}</span>
        </div>
    </div>
</div>

## 🔐 Login Credentials

<div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 20px; margin: 20px 0;">
    <div style="text-align: center; margin-bottom: 20px;">
        <span style="background: #667eea; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">SECURE LOGIN</span>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; text-align: center;">
        <div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Email:</strong>
            <span style="color: #4a5568; font-family: monospace; background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $tenant->admin_email }}</span>
        </div>
        <div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Password:</strong>
            <span style="color: #4a5568; font-family: monospace; background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">Same as configured during registration</span>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 8px; margin-top: 8px;">
                <span style="color: #856404; font-size: 12px; font-weight: 600;">🔒 Security Notice:</span>
                <span style="color: #856404; font-size: 12px;"> It is strongly recommended to change your password after first login.</span>
            </div>
        </div>
    </div>
</div>

@component('mail::button', ['url' => $loginUrl, 'color' => 'primary'])
🚀 Login to Your Account
@endcomponent

## 📚 Documentation & Support

<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 25px 0;">
    <div style="text-align: center; margin-bottom: 20px;">
        <span style="background: #667eea; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600;">📖 COMPREHENSIVE GUIDES</span>
    </div>
    <p style="color: #4a5568; line-height: 1.6; margin-bottom: 20px; text-align: center;">
        Access our comprehensive documentation to learn about all features, best practices, and advanced configurations.
    </p>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
        <div style="text-align: center; padding: 15px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
            <div style="background: #667eea; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 20px; font-weight: bold;">📖</div>
            <a href="http://rotate-vams.com/wiki" style="color: #2d3748; display: block; margin-bottom: 5px;">User Guide</a>
            <span style="color: #718096; font-size: 12px;">Complete feature overview</span>
        </div>
        <div style="text-align: center; padding: 15px; background: white; border-radius: 6px; border: 1px solid #e2e8f0;">
            <div style="background: #667eea; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 20px; font-weight: bold;">⚙</div>
            <a href="http://rotate-vams.com/wiki" style="color: #2d3748; display: block; margin-bottom: 5px;">Admin Guide</a>
            <span style="color: #718096; font-size: 12px;">Configuration & settings</span>
        </div>
    </div>
    <div style="text-align: center;">
        <a href="http://rotate-vams.com/documentations" style="background: #667eea; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">📚 Access Documentation</a>
    </div>
</div>

## 🚀 Getting Started

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 25px; margin: 25px 0; color: white;">
    <h3 style="color: white; margin: 0 0 15px 0; font-size: 18px;">Next Steps:</h3>
    <ol style="color: rgba(255,255,255,0.9); line-height: 1.8; margin: 0; padding-left: 20px;">
        <li><strong>Login</strong> using the credentials above</li>
        <li><strong>Upgrade Your Plan</strong> from the dashboard to unlock all features</li>
        <li><strong>Customize</strong> your virtual airline settings</li>
        <li><strong>Invite Pilots</strong> to join your airline</li>
        <li><strong>Configure Discord</strong> (optional) for notifications</li>
        <li><strong>Add Routes</strong> and start scheduling flights</li>
    </ol>
</div>

## 🔗 Quick Access

<div style="background: #e9ecef; border-radius: 8px; padding: 20px; margin: 25px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div style="text-align: center;">
            <div style="background: #667eea; color: white; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px; font-weight: bold;">📊</div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Dashboard</strong>
            <a href="{{ $loginUrl }}" style="color: #667eea; text-decoration: none; font-size: 14px;">Access Now</a>
        </div>
        <div style="text-align: center;">
            <div style="background: #667eea; color: white; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px; font-weight: bold;">⚙</div>
            <strong style="color: #2d3748; display: block; margin-bottom: 5px;">Admin Panel</strong>
            <a href="{{ $loginUrl }}" style="color: #667eea; text-decoration: none; font-size: 14px;">Manage Settings</a>
        </div>
    </div>
</div>

## 📞 Need Help?

<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center;">
    <p style="color: #4a5568; margin: 0 0 15px 0;">If you need any assistance, our support team is here to help!</p>
    <div style="display: flex; justify-content: center; gap: 20px;">
        <div style="text-align: center;">
            <div style="background: #667eea; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-size: 18px; font-weight: bold;">✉</div>
            <span style="color: #2d3748; font-size: 14px;">Email Support</span>
        </div>
        <div style="text-align: center;">
            <div style="background: #667eea; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-size: 18px; font-weight: bold;">📖</div>
            <span style="color: #2d3748; font-size: 14px;">Documentation</span>
        </div>
    </div>
</div>

<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
    <p style="color: #4a5568; font-size: 14px; margin: 0;">Thank you for choosing us!</p>
    <p style="color: #718096; font-size: 12px; margin: 5px 0 0 0;">Best regards, Rotate-VAMS Team</p>
</div>

---

<div style="text-align: center; margin-top: 20px;">
    <p style="color: #a0aec0; font-size: 12px; font-style: italic; margin: 0;">This is an automated message. Please do not reply to this email.</p>
</div>
@endcomponent 