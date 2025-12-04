<?php

/**
 * SMTP.com Mail Driver
 *
 * Uses SMTP.com REST API v4 for sending emails
 *
 * @package Mailable
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
class SMTPcom_Driver extends Mail_Driver
{

    /**
     * API endpoint URL
     *
     * @var string
     */
    private $api_url = 'https://api.smtp.com/v4/messages';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->driver_name  = 'smtpcom';
        $this->driver_label = 'SMTP.com';

        // Hook into pre_wp_mail to intercept emails when this driver is active
        // This short-circuits wp_mail() if we successfully send via API
        add_filter('pre_wp_mail', array($this, 'intercept_wp_mail'), 10, 2);
    }

    /**
     * Configure PHPMailer for SMTP.com
     *
     * This is a no-op since we use the REST API instead of SMTP
     * The pre_wp_mail filter prevents PHPMailer from being initialized when API send succeeds
     *
     * @param PHPMailer $phpmailer
     * @return void
     */
    public function configure_phpmailer($phpmailer)
    {
        // No-op: We use REST API via pre_wp_mail filter, not SMTP
    }

    /**
     * Intercept wp_mail calls when SMTP.com is the active driver
     *
     * Uses pre_wp_mail filter to short-circuit wp_mail() if API send succeeds
     *
     * @param null|bool $return Short-circuit return value
     * @param array     $atts Email arguments from wp_mail
     * @return null|bool Return true if sent via API, null to let wp_mail continue
     */
    public function intercept_wp_mail($return, $atts)
    {
        // If another filter already short-circuited, respect that
        if (null !== $return) {
            return $return;
        }

        // Only intercept if this driver is active
        $active_driver = get_option('mailable_active_driver', 'sendgrid');
        if ($active_driver !== $this->driver_name) {
            return null; // Let wp_mail continue normally
        }

        // Validate configuration before sending
        $validation = $this->validate_config();
        if (is_wp_error($validation)) {
            return null; // Let PHPMailer handle it or fail gracefully
        }

        // Extract email data - pre_wp_mail receives array with keys: to, subject, message, headers, attachments, embeds
        $to      = isset($atts['to']) ? $atts['to'] : '';
        $subject = isset($atts['subject']) ? $atts['subject'] : '';
        $message = isset($atts['message']) ? $atts['message'] : '';
        $headers = isset($atts['headers']) ? $atts['headers'] : array();
        $attachments = isset($atts['attachments']) ? $atts['attachments'] : array();

        // Validate that we have at least one recipient
        if (empty($to)) {
            return null; // Let PHPMailer handle the error
        }

        // Normalize $to to array for validation
        $to_array = is_array($to) ? $to : (is_string($to) ? explode(',', $to) : array($to));
        $has_valid_recipient = false;
        foreach ($to_array as $email) {
            $email = trim($email);
            if (is_email($email)) {
                $has_valid_recipient = true;
                break;
            }
        }

        if (! $has_valid_recipient) {
            return null; // Let PHPMailer handle the error
        }

        // Send via API
        $result = $this->send_via_api($to, $subject, $message, $headers, $attachments);

        // If API send was successful, return true to short-circuit wp_mail()
        // This prevents PHPMailer from being initialized at all
        return $result ? true : null;
    }

    /**
     * Send email via SMTP.com API
     *
     * @param string|array $to Email address(es)
     * @param string       $subject Email subject
     * @param string       $message Email message
     * @param array        $headers Email headers
     * @param array        $attachments Email attachments
     * @return bool True on success, false on failure
     */
    private function send_via_api($to, $subject, $message, $headers = array(), $attachments = array())
    {
        $api_key = $this->get_option('api_key');
        $channel = $this->get_option('channel');

        if (empty($api_key) || empty($channel)) {
            return false;
        }

        // Validate recipient
        if (empty($to)) {
            return false;
        }

        // Format email data for API
        $body = $this->format_email_for_api($to, $subject, $message, $headers, $attachments);

        // Validate that body has recipients
        if (empty($body['recipients']) || empty($body['recipients']['to'])) {
            return false;
        }

        // Prepare request
        $request_args = array(
            'method'  => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode($body),
            'timeout' => 30,
        );

        // Send request
        $response = wp_remote_post($this->api_url, $request_args);

        // Check for errors
        if (is_wp_error($response)) {
            return false;
        }

        // Check response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return false;
        }

        return true;
    }

    /**
     * Format email data for SMTP.com API
     *
     * @param string|array $to Email address(es)
     * @param string       $subject Email subject
     * @param string       $message Email message
     * @param array        $headers Email headers
     * @param array        $attachments Email attachments
     * @return array Formatted data for API
     */
    private function format_email_for_api($to, $subject, $message, $headers = array(), $attachments = array())
    {
        $channel = $this->get_option('channel');
        $body    = array(
            'channel' => $channel,
            'subject' => $subject,
        );

        // Parse headers
        $parsed_headers = $this->parse_headers($headers);
        $content_type   = isset($parsed_headers['Content-Type']) ? $parsed_headers['Content-Type'] : 'text/html';
        $is_html        = strpos($content_type, 'text/html') !== false;

        // Set originator (from)
        $from_email = $this->get_option('from_email');
        $from_name  = $this->get_option('from_name');

        if (empty($from_email)) {
            // Try to get from headers
            if (isset($parsed_headers['From'])) {
                $from_email = $parsed_headers['From'];
            } else {
                // Use WordPress default
                $from_email = get_option('admin_email');
            }
        }

        $originator = array(
            'from' => array(
                'address' => $from_email,
            ),
        );

        if (! empty($from_name)) {
            $originator['from']['name'] = $from_name;
        }

        // Set reply-to if present
        if (isset($parsed_headers['Reply-To'])) {
            $reply_to = $this->parse_email_address($parsed_headers['Reply-To']);
            if ($reply_to) {
                $originator['reply_to'] = $reply_to;
            }
        }

        $body['originator'] = $originator;

        // Set recipients
        $recipients = array();

        // Parse 'to' addresses
        if (is_string($to)) {
            $to = explode(',', $to);
        }

        $to_recipients = array();
        foreach ((array) $to as $email) {
            $email = trim($email);
            if (is_email($email)) {
                $to_recipients[] = array('address' => $email);
            }
        }
        if (! empty($to_recipients)) {
            $recipients['to'] = $to_recipients;
        }

        // Parse CC and BCC from headers
        if (isset($parsed_headers['Cc'])) {
            $cc_emails = $this->parse_email_list($parsed_headers['Cc']);
            if (! empty($cc_emails)) {
                $recipients['cc'] = $cc_emails;
            }
        }

        if (isset($parsed_headers['Bcc'])) {
            $bcc_emails = $this->parse_email_list($parsed_headers['Bcc']);
            if (! empty($bcc_emails)) {
                $recipients['bcc'] = $bcc_emails;
            }
        }

        if (! empty($recipients)) {
            $body['recipients'] = $recipients;
        }

        // Set body content
        $body_parts = array();

        if ($is_html) {
            $body_parts[] = array(
                'type'    => 'text/html',
                'content' => $message,
                'charset' => 'UTF-8',
            );
        } else {
            $body_parts[] = array(
                'type'    => 'text/plain',
                'content' => $message,
                'charset' => 'UTF-8',
            );
        }

        $body_data = array(
            'parts' => $body_parts,
        );

        // Handle attachments
        if (! empty($attachments)) {
            $attachment_data = array();
            foreach ($attachments as $attachment) {
                $file_content = $this->get_attachment_content($attachment);
                if ($file_content === false) {
                    continue;
                }

                $file_info = $this->get_attachment_info($attachment);
                $attachment_data[] = array(
                    'content'     => chunk_split(base64_encode($file_content)), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
                    'type'        => $file_info['type'],
                    'encoding'    => 'base64',
                    'filename'    => $file_info['filename'],
                    'disposition' => 'attachment',
                    'cid'         => '',
                );
            }

            if (! empty($attachment_data)) {
                $body_data['attachments'] = $attachment_data;
            }
        }

        $body['body'] = $body_data;

        // Set custom headers (excluding standard ones)
        $custom_headers = array();
        $excluded_headers = array('From', 'To', 'Cc', 'Bcc', 'Subject', 'Reply-To', 'Content-Type');
        foreach ($parsed_headers as $name => $value) {
            if (! in_array($name, $excluded_headers, true)) {
                $custom_headers[$name] = $value;
            }
        }

        if (! empty($custom_headers)) {
            $body['custom_headers'] = $custom_headers;
        }

        return $body;
    }

    /**
     * Parse email headers into associative array
     *
     * @param array|string $headers Headers array or string
     * @return array Parsed headers
     */
    private function parse_headers($headers)
    {
        $parsed = array();

        if (is_string($headers)) {
            $headers = explode("\n", $headers);
        }

        foreach ((array) $headers as $header) {
            if (is_string($header)) {
                $header = trim($header);
                if (empty($header)) {
                    continue;
                }

                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $name  = trim($parts[0]);
                    $value = trim($parts[1]);
                    $parsed[$name] = $value;
                }
            } elseif (is_array($header) && isset($header[0]) && isset($header[1])) {
                $parsed[$header[0]] = $header[1];
            }
        }

        return $parsed;
    }

    /**
     * Parse email address string into array with address and optional name
     *
     * @param string $email_string Email string (e.g., "Name <email@example.com>" or "email@example.com")
     * @return array|false Array with 'address' and optionally 'name', or false on failure
     */
    private function parse_email_address($email_string)
    {
        $email_string = trim($email_string);
        if (preg_match('/^(.+?)\s*<(.+?)>$/', $email_string, $matches)) {
            return array(
                'address' => trim($matches[2]),
                'name'    => trim($matches[1]),
            );
        } elseif (is_email($email_string)) {
            return array(
                'address' => $email_string,
            );
        }

        return false;
    }

    /**
     * Parse comma-separated email list
     *
     * @param string $email_list Comma-separated email addresses
     * @return array Array of email address arrays
     */
    private function parse_email_list($email_list)
    {
        $emails = array();
        $list   = explode(',', $email_list);

        foreach ($list as $email) {
            $parsed = $this->parse_email_address(trim($email));
            if ($parsed) {
                $emails[] = $parsed;
            }
        }

        return $emails;
    }

    /**
     * Get attachment file content
     *
     * @param string $attachment_path Attachment file path
     * @return string|false File content or false on failure
     */
    private function get_attachment_content($attachment_path)
    {
        if (! file_exists($attachment_path) || ! is_readable($attachment_path)) {
            return false;
        }

        return file_get_contents($attachment_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    }

    /**
     * Get attachment file info (type and filename)
     *
     * @param string $attachment_path Attachment file path
     * @return array Array with 'type' and 'filename'
     */
    private function get_attachment_info($attachment_path)
    {
        $filename = basename($attachment_path);
        $mime_type = wp_check_filetype($filename);

        return array(
            'type'     => ! empty($mime_type['type']) ? $mime_type['type'] : 'application/octet-stream',
            'filename' => $filename,
        );
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
                'label'       => 'API Key',
                'type'        => 'password',
                'required'    => true,
                'description' => sprintf(
                    // translators: %s: Link to get API key
                    __('Get your API key from <a href="%s" target="_blank">SMTP.com Account Settings</a>.', 'mailable'),
                    'https://my.smtp.com/account?tab=manage_api_keys'
                ),
            ),
            array(
                'key'         => 'channel',
                'label'       => 'Sender Name (Channel)',
                'type'        => 'text',
                'required'    => true,
                'description' => sprintf(
                    // translators: %s: Link to get sender name
                    __('Get your Sender Name from <a href="%s" target="_blank">SMTP.com Account Settings</a>.', 'mailable'),
                    'https://my.smtp.com/account?tab=manage_channels'
                ),
            ),
            array(
                'key'         => 'from_email',
                'label'       => 'From Email',
                'type'        => 'email',
                'description' => __('Default "From" email address. This should match your verified sender in SMTP.com.', 'mailable'),
            ),
            array(
                'key'  => 'from_name',
                'label' => 'From Name',
                'type'  => 'text',
                'description' => __('Default "From" name for your emails.', 'mailable'),
            ),
            array(
                'key'         => 'force_from',
                'label'       => 'Force "From" Settings',
                'type'        => 'checkbox',
                'checkbox_label' => __('Force all emails to use the "From" values above.', 'mailable'),
                'description' => __('Recommended. Prevents other plugins from setting their own "From" headers which might not be verified in SMTP.com.', 'mailable'),
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
        $channel = $this->get_option('channel');

        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('SMTP.com API Key is required.', 'mailable'));
        }

        if (empty($channel)) {
            return new WP_Error('missing_channel', __('SMTP.com Sender Name (Channel) is required.', 'mailable'));
        }

        return true;
    }

    /**
     * Test connection to SMTP.com API
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

        $api_key = $this->get_option('api_key');
        $channel = $this->get_option('channel');

        // Try to make a minimal API request to verify credentials
        // We'll use a test endpoint or send a minimal validation request
        $test_body = array(
            'channel' => $channel,
            'subject' => 'Test Connection',
            'originator' => array(
                'from' => array(
                    'address' => get_option('admin_email'),
                ),
            ),
            'recipients' => array(
                'to' => array(
                    array('address' => get_option('admin_email')),
                ),
            ),
            'body' => array(
                'parts' => array(
                    array(
                        'type'    => 'text/plain',
                        'content' => 'This is a test connection.',
                        'charset' => 'UTF-8',
                    ),
                ),
            ),
        );

        $request_args = array(
            'method'  => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode($test_body),
            'timeout' => 10,
        );

        $response = wp_remote_post($this->api_url, $request_args);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => sprintf(
                    // translators: %s: Error message
                    __('Cannot connect to SMTP.com API. Error: %s', 'mailable'),
                    $response->get_error_message()
                ),
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 200) {
            $messages = array();
            $messages[] = __('Successfully connected to SMTP.com API.', 'mailable');
            $messages[] = sprintf(
                // translators: %s: Channel name
                __('Channel "%s" is configured.', 'mailable'),
                $channel
            );

            $from_email = $this->get_option('from_email');
            if (! empty($from_email)) {
                if (is_email($from_email)) {
                    $messages[] = sprintf(
                        // translators: %s: Email address
                        __('From email "%s" is valid.', 'mailable'),
                        $from_email
                    );
                } else {
                    return array(
                        'success' => false,
                        'message' => sprintf(
                            // translators: %s: Email address
                            __('From email "%s" is not a valid email address.', 'mailable'),
                            $from_email
                        ),
                    );
                }
            } else {
                $messages[] = __('Note: From email is not configured. Make sure to set a verified sender in SMTP.com.', 'mailable');
            }

            return array(
                'success' => true,
                'message' => implode(' ', $messages) . ' ' . __('Ready to send emails via SMTP.com API.', 'mailable'),
            );
        } else {
            // Try to parse error from response
            $error_message = __('API request failed.', 'mailable');
            if (! empty($response_body)) {
                $error_data = json_decode($response_body, true);
                if (isset($error_data['data']['error_key'])) {
                    $error_message = $error_data['data']['error_key'];
                } elseif (isset($error_data['message'])) {
                    $error_message = $error_data['message'];
                }
            }

            return array(
                'success' => false,
                'message' => sprintf(
                    // translators: %1$d: Response code, %2$s: Error message
                    __('SMTP.com API returned error code %1$d: %2$s', 'mailable'),
                    $response_code,
                    $error_message
                ),
            );
        }
    }
}
