<?php

/**
 * SendGrid Mail Driver
 *
 * @package Mailable
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

class SendGrid_Driver extends Mail_Driver
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->driver_name  = 'sendgrid';
        $this->driver_label = 'SendGrid';
    }

    /**
     * Configure PHPMailer for SendGrid
     *
     * @param PHPMailer $phpmailer
     * @return void
     */
    public function configure_phpmailer($phpmailer)
    {
        $api_key = $this->get_option('api_key');

        if (empty($api_key)) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = 'smtp.sendgrid.net';
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = 587;
        $phpmailer->Username   = 'apikey'; // SendGrid mandates this exact username
        $phpmailer->Password   = $api_key;
        $phpmailer->SMTPSecure = 'tls';

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
                'key'         => 'api_key',
                'label'       => 'SendGrid API Key',
                'type'        => 'password',
                'required'    => true,
                'description' => 'Create a "Full Access" or "Mail Send" API Key in your <a href="https://app.sendgrid.com/settings/api_keys" target="_blank">SendGrid Dashboard</a>.',
            ),
            array(
                'key'         => 'from_email',
                'label'       => 'From Email',
                'type'        => 'email',
                'description' => 'This must match a <strong>Verified Sender</strong> in SendGrid.',
            ),
            array(
                'key'  => 'from_name',
                'label' => 'From Name',
                'type'  => 'text',
            ),
            array(
                'key'         => 'force_from',
                'label'       => 'Force "From" Settings',
                'type'        => 'checkbox',
                'checkbox_label' => 'Force all emails to use the values above.',
                'description' => 'Recommended. Prevents other plugins from setting their own "From" headers which might get blocked by SendGrid if not verified.',
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
        $api_key = $this->get_option('api_key');

        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'SendGrid API Key is required.');
        }

        return true;
    }
}
