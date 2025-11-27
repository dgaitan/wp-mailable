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

if (class_exists('Mailable')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>Mailable plugin is already active.</p></div>';
    });
    return;
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

        $active_driver_name = get_option($this->option_active_driver, 'sendgrid');
        $active_driver      = Mail_Driver_Manager::get_driver($active_driver_name);
        $available_drivers  = Mail_Driver_Manager::get_driver_options();
?>
        <div class="wrap">
            <h1>Mailable Settings</h1>
            <p>Configure your email service provider to send emails through WordPress.</p>

            <form action="options.php" method="post">
                <?php
                settings_fields('mailable_settings_group');
                ?>

                <h2>Mail Service Provider</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="mailable_active_driver">Select Provider</label>
                        </th>
                        <td>
                            <select name="<?php echo esc_attr($this->option_active_driver); ?>" id="mailable_active_driver">
                                <?php foreach ($available_drivers as $name => $label) : ?>
                                    <option value="<?php echo esc_attr($name); ?>" <?php selected($active_driver_name, $name); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Choose your email service provider.</p>
                        </td>
                    </tr>
                </table>

                <?php if ($active_driver) : ?>
                    <h2><?php echo esc_html($active_driver->get_label()); ?> Configuration</h2>
                    <table class="form-table">
                        <?php
                        $fields = $active_driver->get_settings_fields();
                        foreach ($fields as $field) :
                        ?>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="mailable_<?php echo esc_attr($active_driver_name); ?>_<?php echo esc_attr($field['key']); ?>">
                                        <?php echo esc_html($field['label']); ?>
                                    </label>
                                </th>
                                <td>
                                    <?php $active_driver->render_settings_field($field); ?>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                    </table>
                <?php endif; ?>

                <h2>Global Settings</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">From Email</th>
                        <td>
                            <input
                                type="email"
                                name="<?php echo esc_attr($this->option_from_email); ?>"
                                value="<?php echo esc_attr(get_option($this->option_from_email)); ?>"
                                class="regular-text" />
                            <p class="description">Global "From" email address (optional). Can be overridden by driver settings.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">From Name</th>
                        <td>
                            <input
                                type="text"
                                name="<?php echo esc_attr($this->option_from_name); ?>"
                                value="<?php echo esc_attr(get_option($this->option_from_name)); ?>"
                                class="regular-text" />
                            <p class="description">Global "From" name (optional). Can be overridden by driver settings.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Force "From" Settings</th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr($this->option_force_from); ?>"
                                    value="1"
                                    <?php checked(1, get_option($this->option_force_from)); ?> />
                                Force all emails to use the "From" values above.
                            </label>
                            <p class="description">Recommended. Prevents other plugins from setting their own "From" headers.</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Send a Test Email</h2>
            <p>Save your settings above first, then use this form to verify everything is working.</p>
            <form method="post" action="">
                <?php wp_nonce_field('mailable_send_test_email', 'mailable_test_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Send To</th>
                        <td>
                            <input
                                type="email"
                                name="mailable_test_email_recipient"
                                class="regular-text"
                                placeholder="you@example.com"
                                required />
                            <input
                                type="submit"
                                name="mailable_send_test"
                                class="button button-secondary"
                                value="Send Test Email" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
<?php
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
