# Mailable Plugin

A WordPress plugin that allows you to send emails using multiple mail service providers through a driver-based architecture.

## Features

- **Driver-based architecture** - Easy to extend with new mail service providers
- **Multiple providers** - Supports SendGrid, Mailpit (development), and easily extensible for Mailgun, AWS SES, etc.
- **Development tools** - Built-in Mailpit support for local email testing
- **Unified interface** - Same settings page for all providers
- **Test email functionality** - Verify your configuration works

## Available Drivers

### SendGrid
- SMTP configuration via SendGrid API
- Verified sender support
- Force "From" header option

### Mailpit (Development)
- Local development email testing tool
- No authentication required
- Configurable host and port (default: localhost:1025)
- Web UI typically on port 8025
- Perfect for local development environments
- See [Mailpit Documentation](https://mailpit.axllent.org/docs/)

## Adding a New Driver

To add a new mail service provider, create a new driver class:

### 1. Create Driver Class

Create a new file in `includes/drivers/class-{provider}-driver.php`:

```php
<?php
/**
 * Mailgun Mail Driver
 *
 * @package Mailable
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mailgun_Driver extends Mail_Driver {

    public function __construct() {
        $this->driver_name  = 'mailgun';
        $this->driver_label = 'Mailgun';
    }

    public function configure_phpmailer( $phpmailer ) {
        $api_key = $this->get_option( 'api_key' );
        $domain  = $this->get_option( 'domain' );

        if ( empty( $api_key ) || empty( $domain ) ) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = 'smtp.mailgun.org';
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = 587;
        $phpmailer->Username   = 'api';
        $phpmailer->Password   = $api_key;
        $phpmailer->SMTPSecure = 'tls';
    }

    public function get_settings_fields() {
        return array(
            array(
                'key'         => 'api_key',
                'label'       => 'Mailgun API Key',
                'type'        => 'password',
                'required'    => true,
                'description' => 'Your Mailgun API key.',
            ),
            array(
                'key'         => 'domain',
                'label'       => 'Mailgun Domain',
                'type'        => 'text',
                'required'    => true,
                'description' => 'Your verified Mailgun domain.',
            ),
        );
    }

    public function validate_config() {
        $api_key = $this->get_option( 'api_key' );
        $domain  = $this->get_option( 'domain' );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_api_key', 'Mailgun API Key is required.' );
        }

        if ( empty( $domain ) ) {
            return new WP_Error( 'missing_domain', 'Mailgun Domain is required.' );
        }

        return true;
    }
}
```

### 2. Load and Register Driver

In `mailable.php`, add:

```php
// Load the driver
require_once MAILABLE_PLUGIN_DIR . 'includes/drivers/class-mailgun-driver.php';

// In the register_drivers() method, add:
Mail_Driver_Manager::register( 'mailgun', 'Mailgun_Driver' );
```

### 3. Alternative: Register via Hook

You can also register drivers from other plugins using the action hook:

```php
add_action( 'mailable_register_drivers', function() {
    Mail_Driver_Manager::register( 'mailgun', 'Mailgun_Driver' );
} );
```

## Driver Interface

All drivers must extend `Mail_Driver` and implement:

- `configure_phpmailer( $phpmailer )` - Configure PHPMailer instance
- `get_settings_fields()` - Return array of settings fields
- `validate_config()` - Validate driver configuration

## Settings Field Types

Supported field types:
- `text` - Text input
- `email` - Email input
- `password` - Password input (masked)
- `textarea` - Textarea
- `checkbox` - Checkbox

## Hooks

### `mailable_register_drivers`
Fires when drivers are being registered. Use this to register custom drivers from other plugins.

```php
add_action( 'mailable_register_drivers', function() {
    Mail_Driver_Manager::register( 'custom_provider', 'Custom_Driver' );
} );
```

## License

GPL2

