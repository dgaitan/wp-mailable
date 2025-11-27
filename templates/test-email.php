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

<h2>Send a Test Email</h2>
<p>Save your settings above first, then use this form to verify everything is working.</p>
<form method="post" action="">
    <?php wp_nonce_field( 'mailable_send_test_email', 'mailable_test_nonce' ); ?>
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

