<?php

/**
 * Mailpit Mail Driver
 *
 * Development email testing tool driver
 * https://mailpit.axllent.org/
 *
 * @package Mailable
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

class Mailpit_Driver extends Mail_Driver
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->driver_name  = 'mailpit';
        $this->driver_label = 'Mailpit (Development)';
    }

    /**
     * Configure PHPMailer for Mailpit
     *
     * @param PHPMailer $phpmailer
     * @return void
     */
    public function configure_phpmailer($phpmailer)
    {
        $host = $this->get_option('host', 'localhost');
        $port = absint($this->get_option('port', 1025));

        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->Port       = $port;
        $phpmailer->SMTPAuth   = false; // Mailpit doesn't require authentication
        $phpmailer->SMTPSecure = ''; // No encryption by default (or use 'tls' if configured)

        // Optional: Use STARTTLS if enabled
        if ($this->get_option('use_tls', false)) {
            $phpmailer->SMTPSecure = 'tls';
        }

        // Force "From" header if enabled
        if ($this->get_option('force_from', false)) {
            $from_email = $this->get_option('from_email');
            $from_name  = $this->get_option('from_name');

            if (! empty($from_email) && is_email($from_email)) {
                $phpmailer->From = $from_email;
            }

            if (! empty($from_name)) {
                $phpmailer->FromName = $from_name;
            }
        }
    }

    /**
     * Get settings fields
     *
     * @return array
     */
    public function get_settings_fields()
    {
        return array(
            array(
                'key'         => 'host',
                'label'       => 'SMTP Host',
                'type'        => 'text',
                'default'     => 'localhost',
                'description' => 'Mailpit SMTP host (usually <code>localhost</code> or <code>127.0.0.1</code>).',
            ),
            array(
                'key'         => 'port',
                'label'       => 'SMTP Port',
                'type'        => 'text',
                'default'     => '1025',
                'description' => 'Mailpit SMTP port (default: <code>1025</code>). Web UI is typically on port <code>8025</code>.',
            ),
            array(
                'key'         => 'use_tls',
                'label'       => 'Use TLS/STARTTLS',
                'type'        => 'checkbox',
                'checkbox_label' => 'Enable TLS encryption',
                'description' => 'Enable if your Mailpit instance uses STARTTLS (usually not needed for local development).',
            ),
            array(
                'key'         => 'from_email',
                'label'       => 'From Email',
                'type'        => 'email',
                'description' => 'Default "From" email address for development emails.',
            ),
            array(
                'key'  => 'from_name',
                'label' => 'From Name',
                'type'  => 'text',
                'description' => 'Default "From" name for development emails.',
            ),
            array(
                'key'         => 'force_from',
                'label'       => 'Force "From" Settings',
                'type'        => 'checkbox',
                'checkbox_label' => 'Force all emails to use the "From" values above.',
                'description' => 'Recommended for development. Ensures consistent sender information.',
            ),
        );
    }

    /**
     * Validate configuration
     *
     * @return bool|WP_Error
     */
    public function validate_config()
    {
        $host = $this->get_option('host', 'localhost');
        $port = absint($this->get_option('port', 1025));

        if (empty($host)) {
            return new WP_Error('missing_host', 'SMTP Host is required.');
        }

        if ($port < 1 || $port > 65535) {
            return new WP_Error('invalid_port', 'SMTP Port must be between 1 and 65535.');
        }

        return true;
    }

    /**
     * Test connection to Mailpit
     *
     * @return array
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

        $host = $this->get_option('host', 'localhost');
        $port = absint($this->get_option('port', 1025));

        // Try to connect to the SMTP server
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);

        if (!$connection) {
            return array(
                'success' => false,
                'message' => sprintf(
                    'Cannot connect to Mailpit at %s:%d. Error: %s (%d). Make sure Mailpit is running.',
                    $host,
                    $port,
                    $errstr ?: 'Connection timeout',
                    $errno ?: 0
                ),
            );
        }

        fclose($connection);

        return array(
            'success' => true,
            'message' => sprintf(
                'Successfully connected to Mailpit at %s:%d. SMTP server is reachable.',
                $host,
                $port
            ),
        );
    }
}
