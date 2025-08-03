# Email Notification Feature Guide

This guide explains the new email notification feature that sends welcome emails to admin users after tenant registration.

## Overview

After successful tenant registration, the system now automatically sends a welcome email to the admin user containing:
- Login credentials (email and password)
- Tenant information (name, domain)
- Quick access links
- Setup instructions
- Next steps guide

## Features

### ✅ What's Included in the Email

1. **Welcome Message** - Personalized greeting with tenant name
2. **Login Credentials** - Email and password for admin access
3. **Tenant Details** - Name, domain, admin callsign
4. **Login Button** - Direct link to the tenant's login page
5. **Setup Summary** - List of what was configured automatically
6. **Next Steps** - Guide for getting started
7. **Quick Links** - Dashboard and admin panel links
8. **Support Information** - Contact details and documentation links

### 📧 Email Template

The email uses a professional, branded template with:
- Clean, modern design
- Responsive layout
- Clear call-to-action buttons
- Comprehensive information
- Professional branding

## Usage

### Register Tenant with Email

```bash
# Basic registration with email
php artisan tenant:register "VA Airlines" va1.rotate.com

# With custom admin credentials
php artisan tenant:register "VA Airlines" va1.rotate.com \
    --admin-email=admin@va1.rotate.com \
    --admin-password=securepassword123 \
    --admin-callsign=VA1ADMIN

# Force registration (overwrite existing)
php artisan tenant:register "VA Airlines" va1.rotate.com --force
```

### Register Tenant Without Email

```bash
# Skip email sending
php artisan tenant:register "VA Airlines" va1.rotate.com --no-email
```

### Test Email Functionality

```bash
# Test with default email
php artisan test:email

# Test with custom email
php artisan test:email --email=your-email@example.com
```

## Email Configuration

### Mail Driver Setup

The system uses Laravel's mail configuration. By default, it's set to `log` driver for development:

```env
MAIL_MAILER=log
```

For production, configure your preferred mail driver:

```env
# SMTP
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls

# Or use services like Mailgun, Postmark, etc.
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.com
MAILGUN_SECRET=your-secret
```

### From Address Configuration

```env
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your App Name"
```

## Error Handling

The email sending is designed to be non-blocking:
- If email fails, tenant registration still completes successfully
- Email errors are logged but don't prevent registration
- Warning messages are displayed in the console
- Registration process continues even if email fails

## Customization

### Email Template

The email template is located at:
```
resources/views/emails/tenant-registration.blade.php
```

### Mailable Class

The email logic is in:
```
app/Mail/TenantRegistrationMail.php
```

### Customization Options

1. **Template Design** - Modify the Blade template for different styling
2. **Content** - Update the email content and messaging
3. **Additional Data** - Add more information to the email
4. **Branding** - Customize colors, logos, and branding
5. **Localization** - Add support for multiple languages

## Troubleshooting

### Email Not Sending

1. **Check Mail Configuration**
   ```bash
   php artisan config:show mail
   ```

2. **Test Email Functionality**
   ```bash
   php artisan test:email --email=your-email@example.com
   ```

3. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Verify Mail Driver**
   - For development: Use `log` driver to see emails in logs
   - For production: Configure proper SMTP or service

### Common Issues

1. **Mail Driver Not Configured**
   - Set `MAIL_MAILER` in `.env`
   - Configure appropriate mail settings

2. **From Address Issues**
   - Ensure `MAIL_FROM_ADDRESS` is set
   - Verify domain reputation for deliverability

3. **Template Errors**
   - Check Blade template syntax
   - Verify mail components are available

## Security Considerations

1. **Password in Email** - Admin password is included in plain text
2. **Email Storage** - Consider email retention policies
3. **Access Control** - Ensure only authorized users can trigger emails
4. **Rate Limiting** - Consider implementing email rate limiting

## Future Enhancements

Potential improvements for the email feature:

1. **Email Templates** - Multiple template options
2. **Localization** - Multi-language support
3. **Email Preferences** - User-configurable email settings
4. **Email Tracking** - Delivery and open tracking
5. **Scheduled Emails** - Follow-up emails and reminders
6. **Email Queues** - Background email processing
7. **Email Templates** - Rich HTML templates with images
8. **Email Analytics** - Track email performance

## Support

For issues with the email functionality:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Test email functionality: `php artisan test:email`
3. Verify mail configuration: `php artisan config:show mail`
4. Check mail driver settings in `.env` file 