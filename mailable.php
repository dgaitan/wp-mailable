<?php

/**
 * Plugin Name: Mailable
 * Plugin URI:  https://mailable.com/
 * Description: A plugin that allows you to send emails using multiple mail service providers (SendGrid, Mailgun, etc.)
 * Version:     2.0.0
 * Author:      David Gaitan
 * License:     GPL2
 */

// Prevent direct access to the file
if (! defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MAILABLE_VERSION', '2.0.0');
define('MAILABLE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MAILABLE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load core classes
require_once MAILABLE_PLUGIN_DIR . 'includes/class-mail-driver.php';
require_once MAILABLE_PLUGIN_DIR . 'includes/class-driver-manager.php';

// Load drivers
require_once MAILABLE_PLUGIN_DIR . 'includes/drivers/class-sendgrid-driver.php';
require_once MAILABLE_PLUGIN_DIR . 'includes/drivers/class-mailpit-driver.php';

/**
 * Main Mailable Plugin Class
 */
class Mailable
{

    /**
     * Option keys
     */
    private $option_active_driver = 'mailable_active_driver';
    private $option_from_email    = 'mailable_from_email';
    private $option_from_name     = 'mailable_from_name';
    private $option_force_from    = 'mailable_force_from';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register default drivers
        $this->register_drivers();

        // Hook into the admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Configure PHPMailer using active driver
        add_action('phpmailer_init', array($this, 'configure_smtp'));

        // Hook for modifying the "From" address/name
        add_filter('wp_mail_from', array($this, 'set_from_email'));
        add_filter('wp_mail_from_name', array($this, 'set_from_name'));
    }

    /**
     * Register available drivers
     *
     * @return void
     */
    private function register_drivers()
    {
        Mail_Driver_Manager::register('sendgrid', 'SendGrid_Driver');
        Mail_Driver_Manager::register('mailpit', 'Mailpit_Driver');

        // Allow other plugins to register drivers
        do_action('mailable_register_drivers');
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function add_admin_menu()
    {
        add_options_page(
            'Mailable Settings',
            'Mailable',
            'manage_options',
            'mailable-settings',
            array($this, 'settings_page_html')
        );
    }

    /**
     * Register settings
     *
     * @return void
     */
    public function register_settings()
    {
        // Register main settings
        register_setting('mailable_settings_group', $this->option_active_driver);
        register_setting('mailable_settings_group', $this->option_from_email, 'sanitize_email');
        register_setting('mailable_settings_group', $this->option_from_name, 'sanitize_text_field');
        register_setting('mailable_settings_group', $this->option_force_from);

        // Register settings for each driver
        $drivers = Mail_Driver_Manager::get_drivers();
        foreach ($drivers as $name => $class) {
            $driver = new $class();
            $fields = $driver->get_settings_fields();

            foreach ($fields as $field) {
                $option_key = 'mailable_' . $name . '_' . $field['key'];
                $sanitize   = $this->get_sanitize_callback($field['type']);
                register_setting('mailable_settings_group', $option_key, $sanitize);
            }
        }
    }

    /**
     * Get sanitize callback for field type
     *
     * @param string $type Field type
     * @return callable
     */
    private function get_sanitize_callback($type)
    {
        switch ($type) {
            case 'email':
                return 'sanitize_email';
            case 'textarea':
                return 'sanitize_textarea_field';
            case 'checkbox':
                return 'absint';
            default:
                return 'sanitize_text_field';
        }
    }

    /**
     * Configure PHPMailer using active driver
     *
     * @param PHPMailer $phpmailer
     * @return void
     */
    public function configure_smtp($phpmailer)
    {
        $driver = Mail_Driver_Manager::get_active_driver();

        if (! $driver) {
            return;
        }

        // Validate configuration
        $validation = $driver->validate_config();
        if (is_wp_error($validation)) {
            return;
        }

        // Configure PHPMailer
        $driver->configure_phpmailer($phpmailer);
    }

    /**
     * Set from email
     *
     * @param string $email
     * @return string
     */
    public function set_from_email($email)
    {
        if (get_option($this->option_force_from)) {
            $configured_email = get_option($this->option_from_email);
            if (is_email($configured_email)) {
                return $configured_email;
            }
        }

        // Try to get from active driver
        $driver = Mail_Driver_Manager::get_active_driver();
        if ($driver && $driver->get_option('force_from')) {
            $driver_email = $driver->get_option('from_email');
            if (is_email($driver_email)) {
                return $driver_email;
            }
        }

        return $email;
    }

    /**
     * Set from name
     *
     * @param string $name
     * @return string
     */
    public function set_from_name($name)
    {
        if (get_option($this->option_force_from)) {
            $configured_name = get_option($this->option_from_name);
            if (! empty($configured_name)) {
                return $configured_name;
            }
        }

        // Try to get from active driver
        $driver = Mail_Driver_Manager::get_active_driver();
        if ($driver && $driver->get_option('force_from')) {
            $driver_name = $driver->get_option('from_name');
            if (! empty($driver_name)) {
                return $driver_name;
            }
        }

        return $name;
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function settings_page_html()
    {
        // Handle test email
        $this->handle_test_email();

        if (! current_user_can('manage_options')) {
            return;
        }

        // Prepare template variables
        $active_driver_name = get_option($this->option_active_driver, 'sendgrid');
        $active_driver      = Mail_Driver_Manager::get_driver($active_driver_name);
        $available_drivers  = Mail_Driver_Manager::get_driver_options();

        // Pass option keys to template
        $option_active_driver = $this->option_active_driver;
        $option_from_email    = $this->option_from_email;
        $option_from_name     = $this->option_from_name;
        $option_force_from    = $this->option_force_from;

        // Load settings page template
        require MAILABLE_PLUGIN_DIR . 'templates/settings-page.php';
    }

    /**
     * Handle test email
     *
     * @return void
     */
    private function handle_test_email()
    {
        if (! isset($_POST['mailable_send_test']) || ! isset($_POST['mailable_test_nonce'])) {
            return;
        }

        if (! wp_verify_nonce($_POST['mailable_test_nonce'], 'mailable_send_test_email')) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        $driver = Mail_Driver_Manager::get_active_driver();
        $driver_name = $driver ? $driver->get_label() : 'Unknown';

        $to      = sanitize_email($_POST['mailable_test_email_recipient']);
        $subject = 'Test Email from Mailable Plugin';
        $message = sprintf(
            'This is a test email sent via %s. If you are reading this, your configuration is correct!',
            $driver_name
        );
        $headers = array('Content-Type: text/html; charset=UTF-8');

        try {
            $result = wp_mail($to, $subject, $message, $headers);

            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p><strong>Success!</strong> Test email sent to ' . esc_html($to) . ' using ' . esc_html($driver_name) . '.</p></div>';
            } else {
                global $phpmailer;
                echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> Email failed to send.</p>';
                if (isset($phpmailer->ErrorInfo)) {
                    echo '<p>Debug Info: ' . esc_html($phpmailer->ErrorInfo) . '</p>';
                }
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>Exception:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
}

// Initialize the plugin
new Mailable();
