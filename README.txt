=== Mailable ===
Contributors: davidgaitan
Tags: email, smtp, sendgrid, transactional email, email delivery
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 2.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A flexible WordPress email plugin with support for multiple mail service providers through a driver-based architecture.

== Description ==

Mailable allows you to send emails from WordPress using various email service providers (SendGrid, Mailpit, and more) through a unified, easy-to-use interface. Perfect for production environments and local development.

= Features =

* **Multiple Email Providers** - Support for SendGrid, Mailpit, and easily extensible for Mailgun, AWS SES, and more
* **Driver-Based Architecture** - Clean, extensible design that makes adding new providers simple
* **Development Tools** - Built-in Mailpit support for local email testing without sending real emails
* **Unified Interface** - Single settings page for all providers with dynamic form switching
* **Connection Testing** - Test your email configuration before sending
* **Test Email Feature** - Send test emails to verify everything works
* **Secure** - Properly sanitizes and validates all inputs
* **Clean UI** - Modern, intuitive WordPress admin interface

= Quick Start =

1. Activate the plugin
2. Navigate to **Settings → Mailable**
3. Select your email provider (SendGrid, Mailpit, etc.)
4. Configure provider-specific settings
5. Test your configuration
6. Save settings

= Available Drivers =

* **SendGrid** - Production-ready email delivery service
* **Mailpit** - Local email testing tool for development

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher

== Installation ==

= From WordPress Admin =

1. Go to **Plugins → Add New**
2. Search for "Mailable"
3. Click **Install Now**
4. Click **Activate**

= Manual Installation =

1. Download the plugin ZIP file
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate**

== Frequently Asked Questions ==

= Why should I use Mailable instead of other email plugins? =

Mailable offers a clean, driver-based architecture that makes it easy to switch between providers or add custom ones. It's designed for both production use and local development with built-in Mailpit support.

= Can I use multiple email providers at once? =

No, Mailable uses one active provider at a time. You can easily switch between providers in the settings page.

= Does Mailable work with WooCommerce/other plugins? =

Yes! Mailable integrates with WordPress's standard email system (`wp_mail()`), so any plugin that uses WordPress email functions will automatically use your configured provider.

= Can I use this in a multisite network? =

Yes, Mailable works with WordPress multisite. Each site can have its own email provider configuration.

= Is my API key secure? =

Yes, all settings are stored securely in WordPress's options table. API keys are stored as password fields and are never displayed in plain text.

= Can I add support for [Provider Name]? =

Yes! Mailable is designed to be extensible. See the documentation for instructions on adding custom drivers or leave a request and we can add the needed driver.

= Does Mailpit work in production? =

Mailpit is designed for local development and testing. For production, use a proper email service like SendGrid, Mailgun, or AWS SES.

== Screenshots ==

1. Settings page with provider selection
2. SendGrid configuration
3. Mailpit configuration for development
4. Test email interface

== Changelog ==

= 2.0.0 =
* Initial public release
* Driver-based architecture
* SendGrid driver
* Mailpit driver for development
* Connection testing
* Test email functionality
* Dynamic form switching
* Clean admin interface

== Upgrade Notice ==

= 2.0.0 =
Initial public release. Install and configure your email provider to start sending emails through Mailable.

== Developer Information ==

Mailable uses a driver-based architecture, making it easy to add support for new email providers. See the plugin documentation for details on creating custom drivers.

== Credits ==

Built with ❤️ for the WordPress community.
Uses PHPMailer for email delivery.
Inspired by Laravel's Mail driver architecture.

