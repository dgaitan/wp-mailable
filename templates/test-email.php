<?php
/**
 * Test Email Template
 *
 * @package Mailable
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h2>Test Connection & Send Email</h2>
<p>Test your email configuration and send a test email to verify everything is working correctly.</p>

<div style="background: #f0f0f1; border-left: 4px solid #2271b1; padding: 12px; margin: 20px 0;">
    <p style="margin: 0;"><strong>Step 1:</strong> Test your connection to verify configuration</p>
    <p style="margin: 5px 0 0 0;"><strong>Step 2:</strong> Send a test email to verify end-to-end delivery</p>
</div>

<!-- Connection Test -->
<h3>Connection Test</h3>
<p>Verify that your email service provider configuration is correct.</p>
<form method="post" action="" style="margin-bottom: 30px;">
    <?php wp_nonce_field( 'mailable_send_test_email', 'mailable_test_connection_nonce' ); ?>
    <p>
        <input
            type="submit"
            name="mailable_test_connection"
            class="button button-primary"
            value="Test Connection" />
        <span class="description" style="margin-left: 10px;">This will validate your configuration without sending an email.</span>
    </p>
</form>

<!-- Test Email -->
<h3>Send Test Email</h3>
<p>Send an actual test email to verify end-to-end delivery.</p>
<form method="post" action="">
    <?php wp_nonce_field( 'mailable_send_test_email', 'mailable_test_email_nonce' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">
                <label for="mailable_test_email_recipient">Send To</label>
            </th>
            <td>
                <input
                    type="email"
                    name="mailable_test_email_recipient"
                    id="mailable_test_email_recipient"
                    class="regular-text"
                    placeholder="you@example.com"
                    required />
                <p class="description">Enter the email address where you want to receive the test email.</p>
            </td>
        </tr>
    </table>
    <p>
        <input
            type="submit"
            name="mailable_send_test"
            class="button button-secondary"
            value="Send Test Email" />
    </p>
</form>

