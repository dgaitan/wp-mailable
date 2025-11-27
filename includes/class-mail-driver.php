<?php

/**
 * Abstract Mail Driver Class
 *
 * All mail service drivers must extend this class.
 *
 * @package Mailable
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

abstract class Mail_Driver
{

    /**
     * Driver name/slug
     *
     * @var string
     */
    protected $driver_name;

    /**
     * Driver display name
     *
     * @var string
     */
    protected $driver_label;

    /**
     * Get driver name
     *
     * @return string
     */
    public function get_name()
    {
        return $this->driver_name;
    }

    /**
     * Get driver label
     *
     * @return string
     */
    public function get_label()
    {
        return $this->driver_label;
    }

    /**
     * Configure PHPMailer with driver-specific settings
     *
     * @param PHPMailer $phpmailer The PHPMailer instance
     * @return void
     */
    abstract public function configure_phpmailer($phpmailer);

    /**
     * Get settings fields for the admin page
     *
     * @return array Array of setting field definitions
     */
    abstract public function get_settings_fields();

    /**
     * Validate driver configuration
     *
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    abstract public function validate_config();

    /**
     * Test connection to mail service
     *
     * This method can be overridden by drivers to perform connection tests
     * Default implementation just validates configuration
     *
     * @return array Array with 'success' (bool) and 'message' (string)
     */
    public function test_connection()
    {
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

    /**
     * Get option value for this driver
     *
     * @param string $key Option key
     * @param mixed  $default Default value
     * @return mixed
     */
    public function get_option($key, $default = '')
    {
        $full_key = 'mailable_' . $this->driver_name . '_' . $key;
        return get_option($full_key, $default);
    }

    /**
     * Render a settings field
     *
     * @param array $field Field configuration
     * @return void
     */
    public function render_settings_field($field)
    {
        $value = $this->get_option($field['key'], $field['default'] ?? '');
        $name  = 'mailable_' . $this->driver_name . '_' . $field['key'];
        $id    = sanitize_html_class($name);

        switch ($field['type']) {
            case 'text':
            case 'email':
            case 'password':
?>
                <input
                    type="<?php echo esc_attr($field['type']); ?>"
                    name="<?php echo esc_attr($name); ?>"
                    id="<?php echo esc_attr($id); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    class="regular-text"
                    <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?> />
                <?php
                if (! empty($field['description'])) {
                    echo '<p class="description">' . wp_kses_post($field['description']) . '</p>';
                }
                break;

            case 'textarea':
                ?>
                <textarea
                    name="<?php echo esc_attr($name); ?>"
                    id="<?php echo esc_attr($id); ?>"
                    class="large-text"
                    rows="<?php echo esc_attr($field['rows'] ?? 5); ?>"
                    <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>><?php echo esc_textarea($value); ?></textarea>
                <?php
                if (! empty($field['description'])) {
                    echo '<p class="description">' . wp_kses_post($field['description']) . '</p>';
                }
                break;

            case 'checkbox':
                ?>
                <label>
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr($name); ?>"
                        id="<?php echo esc_attr($id); ?>"
                        value="1"
                        <?php checked(1, $value); ?> />
                    <?php echo isset($field['checkbox_label']) ? esc_html($field['checkbox_label']) : (isset($field['label']) ? esc_html($field['label']) : ''); ?>
                </label>
<?php
                if (! empty($field['description'])) {
                    echo '<p class="description">' . wp_kses_post($field['description']) . '</p>';
                }
                break;
        }
    }
}
