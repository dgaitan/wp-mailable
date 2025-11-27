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

$active_driver = Mail_Driver_Manager::get_active_driver();
$driver_label = $active_driver ? $active_driver->get_label() : 'No Provider';
?>

<div class="mailable-info-box">
    <p><strong>Step 1:</strong> Test your connection to verify configuration</p>
    <p><strong>Step 2:</strong> Send a test email to verify end-to-end delivery</p>
</div>

<!-- Connection Test Card -->
<div class="mailable-card">
    <div class="mailable-card-header">
        <h3 class="mailable-card-title">
            <svg class="mailable-card-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Connection Test
        </h3>
    </div>
    <p style="margin: 0 0 20px 0; color: #6b7280;">Verify that your email service provider configuration is correct.</p>
    <form method="post" action="">
        <?php wp_nonce_field( 'mailable_send_test_email', 'mailable_test_connection_nonce' ); ?>
        <button type="submit" name="mailable_test_connection" class="mailable-button mailable-button-success">
            <svg class="mailable-button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Test Connection
        </button>
        <p class="mailable-form-description" style="margin-top: 12px;">This will validate your configuration without sending an email.</p>
    </form>
</div>

<!-- Send Test Email Card -->
<div class="mailable-card">
    <div class="mailable-card-header">
        <h3 class="mailable-card-title">
            <svg class="mailable-card-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Send Test Email
        </h3>
    </div>
    <p style="margin: 0 0 20px 0; color: #6b7280;">Send an actual test email to verify end-to-end delivery.</p>
    <form method="post" action="">
        <?php wp_nonce_field( 'mailable_send_test_email', 'mailable_test_email_nonce' ); ?>
        <div class="mailable-form-group">
            <label for="mailable_test_email_recipient" class="mailable-form-label required">Send To</label>
            <input
                type="email"
                name="mailable_test_email_recipient"
                id="mailable_test_email_recipient"
                class="mailable-form-input"
                placeholder="you@example.com"
                required />
            <p class="mailable-form-description">Enter the email address where you want to receive the test email.</p>
        </div>
        <button type="submit" name="mailable_send_test" class="mailable-button mailable-button-secondary">
            <svg class="mailable-button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Send Test Email
        </button>
    </form>
</div>
