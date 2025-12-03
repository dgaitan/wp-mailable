<?php

/**
 * Plugin Name: Mailable
 * Plugin URI:  https://wordpress.org/plugins/mailable/
 * Description: A flexible WordPress email plugin with support for multiple mail service providers (SendGrid, Mailpit, and more) through a driver-based architecture.
 * Version:     2.0.1
 * Author:      David Gaitan
 * Author URI:  https://profiles.wordpress.org/david-gaitan/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mailable
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
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

        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_scripts($hook)
    {
        // Only load on our settings page
        if ($hook !== 'settings_page_mailable-settings') {
            return;
        }

        wp_enqueue_style(
            'mailable-admin-settings',
            MAILABLE_PLUGIN_URL . 'css/admin-settings.css',
            array(),
            MAILABLE_VERSION
        );

        wp_enqueue_script(
            'mailable-admin-settings',
            MAILABLE_PLUGIN_URL . 'js/admin-settings.js',
            array('jquery'),
            MAILABLE_VERSION,
            true
        );
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
            __('Mailable Settings', 'mailable'),
            __('Mailable', 'mailable'),
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
        register_setting('mailable_settings_group', $this->option_active_driver, 'sanitize_text_field');
        register_setting('mailable_settings_group', $this->option_from_email, 'sanitize_email');
        register_setting('mailable_settings_group', $this->option_from_name, 'sanitize_text_field');
        register_setting('mailable_settings_group', $this->option_force_from, 'absint');

        // Register settings for each driver (no validation - we'll validate on save)
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

        // Add validation on settings save
        add_action('admin_init', array($this, 'validate_active_driver_settings'), 20);
    }

    /**
     * Validate active driver settings
     *
     * @return void
     */
    public function validate_active_driver_settings()
    {
        // Only validate on our settings page
        if (!isset($_POST['option_page']) || !isset($_POST['_wpnonce']) || $_POST['option_page'] !== 'mailable_settings_group') {
            return;
        }

        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(wp_unslash($_POST['_wpnonce']), 'mailable_settings_group-options')) {
            return;
        }

        $active_driver_name = isset($_POST['mailable_active_driver'])
            ? sanitize_text_field(wp_unslash($_POST['mailable_active_driver']))
            : get_option('mailable_active_driver', 'sendgrid');

        $driver = Mail_Driver_Manager::get_driver($active_driver_name);

        if (!$driver) {
            return;
        }

        // Check required fields from POST values
        $fields = $driver->get_settings_fields();
        foreach ($fields as $field) {
            if (isset($field['required']) && $field['required']) {
                $option_key = 'mailable_' . $active_driver_name . '_' . $field['key'];
                $sanitize_callback = $this->get_sanitize_callback($field['type']);
                $value = isset($_POST[$option_key]) ? call_user_func($sanitize_callback, wp_unslash($_POST[$option_key])) : '';

                if (empty($value)) {
                    // translators: %1$s: Field label, %2$s: Driver label
                    add_settings_error(
                        $option_key,
                        'required_field',
                        sprintf(__('%1$s is required for %2$s.', 'mailable'), $field['label'], $driver->get_label())
                    );
                }
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
     * Handle test email and connection test
     *
     * @return void
     */
    private function handle_test_email()
    {
        // Handle connection test
        if (isset($_POST['mailable_test_connection'])) {
            $nonce_key = isset($_POST['mailable_test_connection_nonce']) ? 'mailable_test_connection_nonce' : 'mailable_test_nonce';
            if (isset($_POST[$nonce_key]) && wp_verify_nonce(wp_unslash($_POST[$nonce_key]), 'mailable_send_test_email')) {
                $this->handle_connection_test();
            }
            return;
        }

        // Handle test email
        if (! isset($_POST['mailable_send_test'])) {
            return;
        }

        $nonce_key = isset($_POST['mailable_test_email_nonce']) ? 'mailable_test_email_nonce' : 'mailable_test_nonce';
        if (! isset($_POST[$nonce_key]) || ! wp_verify_nonce(wp_unslash($_POST[$nonce_key]), 'mailable_send_test_email')) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        $driver = Mail_Driver_Manager::get_active_driver();

        if (! $driver) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__('Error:', 'mailable') . '</strong> ' . esc_html__('No active driver configured.', 'mailable') . '</p></div>';
            return;
        }

        $driver_name = $driver->get_label();

        // Test connection first
        $connection_test = $driver->test_connection();
        if (! $connection_test['success']) {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html__('Configuration Issue:', 'mailable') . '</strong> ' . esc_html($connection_test['message']) . '</p><p>' . esc_html__('Please check your settings before sending a test email.', 'mailable') . '</p></div>';
            return;
        }

        $to = isset($_POST['mailable_test_email_recipient']) ? sanitize_email(wp_unslash($_POST['mailable_test_email_recipient'])) : '';
        if (empty($to) || ! is_email($to)) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__('Error:', 'mailable') . '</strong> ' . esc_html__('Invalid email address.', 'mailable') . '</p></div>';
            return;
        }

        $subject = __('Test Email from Mailable Plugin', 'mailable');
        $message = sprintf(
            '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <h2 style="color: #0073aa;">%s</h2>
                <p>%s <strong>%s</strong>.</p>
                <p>%s</p>
                <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
                <p style="color: #666; font-size: 12px;">
                    <strong>%s</strong> %s<br>
                    <strong>%s</strong> %s<br>
                    <strong>%s</strong> %s
                </p>
            </body></html>',
            esc_html__('Test Email Successful!', 'mailable'),
            esc_html__('This is a test email sent via', 'mailable'),
            esc_html($driver_name),
            esc_html__('If you are reading this, your email configuration is working correctly!', 'mailable'),
            esc_html__('Sent:', 'mailable'),
            current_time('mysql'),
            esc_html__('Driver:', 'mailable'),
            esc_html($driver_name),
            esc_html__('From:', 'mailable'),
            esc_html(get_option($this->option_from_email, get_option('admin_email')))
        );
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // Capture PHPMailer errors
        global $phpmailer;
        $phpmailer_errors = array();

        try {
            // Add error handler to capture PHPMailer errors
            add_action('phpmailer_init', function ($phpmailer) use (&$phpmailer_errors) {
                if (! empty($phpmailer->ErrorInfo)) {
                    $phpmailer_errors[] = $phpmailer->ErrorInfo;
                }
            }, 999);

            $result = wp_mail($to, $subject, $message, $headers);

            if ($result) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>✓ ' . esc_html__('Success!', 'mailable') . '</strong> ' . esc_html__('Test email sent successfully!', 'mailable') . '</p>';
                echo '<ul style="margin: 10px 0 0 20px;">';
                echo '<li><strong>' . esc_html__('Recipient:', 'mailable') . '</strong> ' . esc_html($to) . '</li>';
                echo '<li><strong>' . esc_html__('Driver:', 'mailable') . '</strong> ' . esc_html($driver_name) . '</li>';
                echo '<li><strong>' . esc_html__('Connection:', 'mailable') . '</strong> ' . esc_html($connection_test['message']) . '</li>';
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>✗ ' . esc_html__('Error:', 'mailable') . '</strong> ' . esc_html__('Email failed to send.', 'mailable') . '</p>';

                if (! empty($phpmailer_errors)) {
                    echo '<p><strong>' . esc_html__('PHPMailer Errors:', 'mailable') . '</strong></p><ul style="margin: 10px 0 0 20px;">';
                    foreach ($phpmailer_errors as $error) {
                        echo '<li>' . esc_html($error) . '</li>';
                    }
                    echo '</ul>';
                } elseif (isset($phpmailer->ErrorInfo) && ! empty($phpmailer->ErrorInfo)) {
                    echo '<p><strong>' . esc_html__('Debug Info:', 'mailable') . '</strong> ' . esc_html($phpmailer->ErrorInfo) . '</p>';
                } else {
                    echo '<p>' . esc_html__('No specific error information available. Please check your configuration and server logs.', 'mailable') . '</p>';
                }
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>' . esc_html__('Exception:', 'mailable') . '</strong> ' . esc_html($e->getMessage()) . '</p>';
            echo '<p><strong>' . esc_html__('File:', 'mailable') . '</strong> ' . esc_html($e->getFile()) . ':' . esc_html($e->getLine()) . '</p>';
            echo '</div>';
        }
    }

    /**
     * Handle connection test
     *
     * @return void
     */
    private function handle_connection_test()
    {
        $nonce_key = isset($_POST['mailable_test_connection_nonce']) ? 'mailable_test_connection_nonce' : 'mailable_test_nonce';
        if (! isset($_POST[$nonce_key]) || ! wp_verify_nonce(wp_unslash($_POST[$nonce_key]), 'mailable_send_test_email')) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        $driver = Mail_Driver_Manager::get_active_driver();

        if (! $driver) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__('Error:', 'mailable') . '</strong> ' . esc_html__('No active driver configured.', 'mailable') . '</p></div>';
            return;
        }

        $driver_name = $driver->get_label();
        $test_result = $driver->test_connection();

        if ($test_result['success']) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>✓ ' . esc_html__('Connection Test Passed!', 'mailable') . '</strong></p>';
            echo '<ul style="margin: 10px 0 0 20px;">';
            echo '<li><strong>' . esc_html__('Driver:', 'mailable') . '</strong> ' . esc_html($driver_name) . '</li>';
            echo '<li><strong>' . esc_html__('Status:', 'mailable') . '</strong> ' . esc_html($test_result['message']) . '</li>';
            echo '</ul>';
            echo '<p style="margin-top: 10px;">' . esc_html__('Your configuration looks good. You can now send a test email to verify end-to-end delivery.', 'mailable') . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>✗ ' . esc_html__('Connection Test Failed', 'mailable') . '</strong></p>';
            echo '<ul style="margin: 10px 0 0 20px;">';
            echo '<li><strong>' . esc_html__('Driver:', 'mailable') . '</strong> ' . esc_html($driver_name) . '</li>';
            echo '<li><strong>' . esc_html__('Error:', 'mailable') . '</strong> ' . esc_html($test_result['message']) . '</li>';
            echo '</ul>';
            echo '<p style="margin-top: 10px;">' . esc_html__('Please check your configuration settings and try again.', 'mailable') . '</p>';
            echo '</div>';
        }
    }
}

// Initialize the plugin
new Mailable();
