# Mailable

**A flexible WordPress email plugin with support for multiple mail service providers through a driver-based architecture.**

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL%202.0-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Mailable allows you to send emails from WordPress using various email service providers (SendGrid, Mailpit, and more) through a unified, easy-to-use interface. Perfect for production environments and local development.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Available Drivers](#available-drivers)
- [Usage](#usage)
- [Developer Guide](#developer-guide)
- [FAQ](#faq)
- [Support](#support)
- [Changelog](#changelog)
- [License](#license)

## Features

- 🚀 **Multiple Email Providers** - Support for SendGrid, Mailpit, and easily extensible for Mailgun, AWS SES, and more
- 🏗️ **Driver-Based Architecture** - Clean, extensible design that makes adding new providers simple
- 🧪 **Development Tools** - Built-in Mailpit support for local email testing without sending real emails
- 🎯 **Unified Interface** - Single settings page for all providers with dynamic form switching
- ✅ **Connection Testing** - Test your email configuration before sending
- 📧 **Test Email Feature** - Send test emails to verify everything works
- 🔒 **Secure** - Properly sanitizes and validates all inputs
- 🎨 **Clean UI** - Modern, intuitive WordPress admin interface

## Installation

### From WordPress Admin

1. Go to **Plugins → Add New**
2. Search for "Mailable"
3. Click **Install Now**
4. Click **Activate**

### Manual Installation

1. Download the plugin ZIP file
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Click **Activate**

### Via WP-CLI

```bash
wp plugin install mailable --activate
```

## Quick Start

1. **Activate the Plugin**
   - Navigate to **Settings → Mailable** in your WordPress admin

2. **Select Your Email Provider**
   - Choose from the available providers (SendGrid, Mailpit, etc.)

3. **Configure Provider Settings**
   - Enter your provider-specific credentials
   - For SendGrid: API key and verified sender email
   - For Mailpit: Host and port (defaults work for most setups)

4. **Test Your Configuration**
   - Click **Test Connection** to verify your settings
   - Send a test email to confirm everything works

5. **Save Settings**
   - Click **Save Changes**
   - Your WordPress site will now send emails through your configured provider!

## Configuration

### Global Settings

Mailable provides global settings that apply to all email providers:

- **From Email** - Default sender email address (optional, can be overridden by driver)
- **From Name** - Default sender name (optional, can be overridden by driver)
- **Force "From" Settings** - Recommended. Prevents other plugins from overriding your email headers

### Provider-Specific Settings

Each provider has its own configuration options. See the [Available Drivers](#available-drivers) section for details.

## Available Drivers

### SendGrid

**Production-ready email delivery service**

#### Setup Instructions

1. **Get Your SendGrid API Key**
   - Sign up at [SendGrid](https://sendgrid.com/)
   - Go to **Settings → API Keys**
   - Create a new API key with "Full Access" or "Mail Send" permissions
   - Copy the API key (starts with `SG.`)

2. **Verify Your Sender**
   - Go to **Settings → Sender Authentication**
   - Verify a sender email address or domain

3. **Configure in WordPress**
   - Go to **Settings → Mailable**
   - Select **SendGrid** as your provider
   - Enter your API key
   - Enter your verified sender email
   - Optionally set a "From Name"
   - Enable "Force From Settings" (recommended)

#### Configuration Options

- **SendGrid API Key** (required) - Your SendGrid API key
- **From Email** (optional) - Verified sender email address
- **From Name** (optional) - Display name for emails
- **Force "From" Settings** (optional) - Override other plugins' email headers

### Mailpit (Development)

**Local email testing tool - perfect for development**

#### Setup Instructions

1. **Install Mailpit**
   ```bash
   # macOS (via Homebrew)
   brew install mailpit
   
   # Or download from https://github.com/axllent/mailpit/releases
   ```

2. **Start Mailpit**
   ```bash
   mailpit
   ```
   - SMTP server runs on `localhost:1025`
   - Web UI runs on `http://localhost:8025`

3. **Configure in WordPress**
   - Go to **Settings → Mailable**
   - Select **Mailpit (Development)** as your provider
   - Default settings (localhost:1025) work for most setups
   - Adjust host/port if needed

4. **View Emails**
   - Open `http://localhost:8025` in your browser
   - All emails sent from WordPress will appear here

#### Configuration Options

- **SMTP Host** (default: `localhost`) - Mailpit SMTP host
- **SMTP Port** (default: `1025`) - Mailpit SMTP port
- **Use TLS/STARTTLS** (optional) - Enable if your Mailpit uses encryption
- **From Email** (optional) - Default sender email for development
- **From Name** (optional) - Default sender name for development
- **Force "From" Settings** (optional) - Ensure consistent sender info

#### Benefits

- ✅ No authentication required
- ✅ Works offline
- ✅ View all emails in a web interface
- ✅ Perfect for testing email templates
- ✅ No risk of sending test emails to real addresses

## Usage

### Sending Emails

Once configured, Mailable works automatically with WordPress's built-in email functions:

```php
// WordPress will use your configured provider
wp_mail(
    'recipient@example.com',
    'Subject',
    'Message body',
    array('Content-Type: text/html; charset=UTF-8')
);
```

### Testing Your Configuration

1. **Test Connection**
   - Go to **Settings → Mailable**
   - Scroll to **Test Connection & Send Email**
   - Click **Test Connection**
   - Verify the connection status

2. **Send Test Email**
   - Enter your email address
   - Click **Send Test Email**
   - Check your inbox (or Mailpit UI if using Mailpit)

### Using Hooks

Mailable integrates with WordPress's email system, so all standard WordPress email hooks work:

```php
// Modify email before sending
add_filter('wp_mail', function($args) {
    // Modify $args['to'], $args['subject'], etc.
    return $args;
});

// Modify email headers
add_filter('wp_mail_headers', function($headers) {
    $headers[] = 'X-Custom-Header: value';
    return $headers;
});
```

## Developer Guide

### Adding a Custom Driver

Mailable uses a driver-based architecture, making it easy to add support for new email providers.

#### Step 1: Create Driver Class

Create a new file: `includes/drivers/class-{provider}-driver.php`

```php
<?php
/**
 * Custom Provider Mail Driver
 *
 * @package Mailable
 */

if (!defined('ABSPATH')) {
    exit;
}

class Custom_Provider_Driver extends Mail_Driver {

    public function __construct() {
        $this->driver_name  = 'custom_provider';
        $this->driver_label = 'Custom Provider';
    }

    public function configure_phpmailer($phpmailer) {
        $api_key = $this->get_option('api_key');
        $host    = $this->get_option('host', 'smtp.customprovider.com');

        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = 587;
        $phpmailer->Username   = 'apikey';
        $phpmailer->Password   = $api_key;
        $phpmailer->SMTPSecure = 'tls';
    }

    public function get_settings_fields() {
        return array(
            array(
                'key'         => 'api_key',
                'label'       => 'API Key',
                'type'        => 'password',
                'required'    => true,
                'description' => 'Your Custom Provider API key.',
            ),
            array(
                'key'         => 'host',
                'label'       => 'SMTP Host',
                'type'        => 'text',
                'default'     => 'smtp.customprovider.com',
                'description' => 'SMTP server hostname.',
            ),
        );
    }

    public function validate_config() {
        $api_key = $this->get_option('api_key');

        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'API Key is required.');
        }

        return true;
    }

    public function test_connection() {
        // Optional: Override for custom connection testing
        $validation = $this->validate_config();
        
        if (is_wp_error($validation)) {
            return array(
                'success' => false,
                'message' => $validation->get_error_message(),
            );
        }

        return array(
            'success' => true,
            'message' => 'Configuration is valid. Ready to send emails.',
        );
    }
}
```

#### Step 2: Register the Driver

**Option A: Register in Plugin**

In `mailable.php`, add to the `register_drivers()` method:

```php
require_once MAILABLE_PLUGIN_DIR . 'includes/drivers/class-custom-provider-driver.php';
Mail_Driver_Manager::register('custom_provider', 'Custom_Provider_Driver');
```

**Option B: Register via Hook (Recommended for Third-Party)**

In your own plugin or theme:

```php
add_action('mailable_register_drivers', function() {
    require_once plugin_dir_path(__FILE__) . 'drivers/class-custom-provider-driver.php';
    Mail_Driver_Manager::register('custom_provider', 'Custom_Provider_Driver');
});
```

### Driver Interface

All drivers must extend `Mail_Driver` and implement:

| Method | Description | Required |
|--------|-------------|----------|
| `configure_phpmailer($phpmailer)` | Configure PHPMailer instance with provider settings | ✅ Yes |
| `get_settings_fields()` | Return array of settings field definitions | ✅ Yes |
| `validate_config()` | Validate driver configuration, return `true` or `WP_Error` | ✅ Yes |
| `test_connection()` | Test connection to provider (optional override) | ⚠️ Optional |

### Settings Field Types

Supported field types:

- `text` - Text input
- `email` - Email input with validation
- `password` - Password input (masked)
- `textarea` - Multi-line text input
- `checkbox` - Checkbox input

Field definition structure:

```php
array(
    'key'            => 'field_key',           // Required: Unique identifier
    'label'          => 'Field Label',        // Required: Display label
    'type'           => 'text',                // Required: Field type
    'default'        => 'default_value',       // Optional: Default value
    'required'       => true,                  // Optional: Mark as required
    'description'    => 'Help text',          // Optional: Description below field
    'checkbox_label' => 'Checkbox text',      // Optional: For checkbox fields
)
```

### Hooks and Filters

#### Actions

**`mailable_register_drivers`**

Register custom drivers from other plugins or themes.

```php
add_action('mailable_register_drivers', function() {
    Mail_Driver_Manager::register('my_provider', 'My_Provider_Driver');
});
```

#### Filters

Mailable integrates with WordPress's email system, so all standard WordPress email filters work:

- `wp_mail` - Modify email arguments before sending
- `wp_mail_from` - Modify sender email
- `wp_mail_from_name` - Modify sender name
- `wp_mail_headers` - Modify email headers
- `phpmailer_init` - Modify PHPMailer instance (used by Mailable)

## FAQ

### Why should I use Mailable instead of other email plugins?

Mailable offers a clean, driver-based architecture that makes it easy to switch between providers or add custom ones. It's designed for both production use and local development with built-in Mailpit support.

### Can I use multiple email providers at once?

No, Mailable uses one active provider at a time. You can easily switch between providers in the settings page.

### Does Mailable work with WooCommerce/other plugins?

Yes! Mailable integrates with WordPress's standard email system (`wp_mail()`), so any plugin that uses WordPress email functions will automatically use your configured provider.

### Can I use this in a multisite network?

Yes, Mailable works with WordPress multisite. Each site can have its own email provider configuration.

### Is my API key secure?

Yes, all settings are stored securely in WordPress's options table. API keys are stored as password fields and are never displayed in plain text.

### Can I add support for [Provider Name]?

Yes! Mailable is designed to be extensible. See the [Developer Guide](#developer-guide) for instructions on adding custom drivers. You can also submit a feature request or contribute a driver.

### Does Mailpit work in production?

Mailpit is designed for local development and testing. For production, use a proper email service like SendGrid, Mailgun, or AWS SES.

### What PHP version is required?

Mailable requires PHP 7.4 or higher.

## Support

### Getting Help

- 📖 **Documentation** - Check this README and inline code comments
- 🐛 **Bug Reports** - Open an issue on GitHub
- 💡 **Feature Requests** - Submit via GitHub issues
- 💬 **Questions** - Use GitHub Discussions

### Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

### Reporting Issues

When reporting bugs, please include:

- WordPress version
- PHP version
- Mailable version
- Steps to reproduce
- Error messages (if any)
- Screenshots (if applicable)

## Changelog

### 2.0.0

- ✨ Initial public release
- ✨ Driver-based architecture
- ✨ SendGrid driver
- ✨ Mailpit driver for development
- ✨ Connection testing
- ✨ Test email functionality
- ✨ Dynamic form switching
- ✨ Clean admin interface

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 David Gaitan

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Credits

- Built with ❤️ for the WordPress community
- Uses [PHPMailer](https://github.com/PHPMailer/PHPMailer) for email delivery
- Inspired by Laravel's Mail driver architecture

---

**Made with ❤️ by [David Gaitan](https://github.com/davidgaitan)**
