<?php
/**
 * Global Settings Template
 *
 * @package Mailable
 *
 * @var string $option_from_email
 * @var string $option_from_name
 * @var string $option_force_from
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="mailable-card">
    <div class="mailable-card-header">
        <h3 class="mailable-card-title">
            <svg class="mailable-card-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            <?php esc_html_e('Global Settings', 'mailable'); ?>
        </h3>
    </div>
    
    <div class="mailable-form-group">
        <label for="<?php echo esc_attr( $option_from_email ); ?>" class="mailable-form-label"><?php esc_html_e('From Email', 'mailable'); ?></label>
        <input
            type="email"
            name="<?php echo esc_attr( $option_from_email ); ?>"
            id="<?php echo esc_attr( $option_from_email ); ?>"
            value="<?php echo esc_attr( get_option( $option_from_email ) ); ?>"
            class="mailable-form-input" />
        <p class="mailable-form-description"><?php esc_html_e('Global "From" email address (optional). Can be overridden by driver settings.', 'mailable'); ?></p>
    </div>

    <div class="mailable-form-group">
        <label for="<?php echo esc_attr( $option_from_name ); ?>" class="mailable-form-label"><?php esc_html_e('From Name', 'mailable'); ?></label>
        <input
            type="text"
            name="<?php echo esc_attr( $option_from_name ); ?>"
            id="<?php echo esc_attr( $option_from_name ); ?>"
            value="<?php echo esc_attr( get_option( $option_from_name ) ); ?>"
            class="mailable-form-input" />
        <p class="mailable-form-description"><?php esc_html_e('Global "From" name (optional). Can be overridden by driver settings.', 'mailable'); ?></p>
    </div>

    <div class="mailable-form-group">
        <div class="mailable-toggle">
            <div class="mailable-toggle-switch <?php echo get_option( $option_force_from ) ? 'active' : ''; ?>" data-toggle-target="<?php echo esc_attr( $option_force_from ); ?>"></div>
            <div>
                <label for="<?php echo esc_attr( $option_force_from ); ?>" class="mailable-toggle-label <?php echo get_option( $option_force_from ) ? 'active' : ''; ?>">
                    <?php esc_html_e('Force "From" Settings', 'mailable'); ?>
                </label>
                <input
                    type="checkbox"
                    name="<?php echo esc_attr( $option_force_from ); ?>"
                    id="<?php echo esc_attr( $option_force_from ); ?>"
                    value="1"
                    <?php checked( 1, get_option( $option_force_from ) ); ?>
                    style="display: none;" />
                <p class="mailable-toggle-description"><?php esc_html_e('Recommended. Prevents other plugins from setting their own "From" headers.', 'mailable'); ?></p>
            </div>
        </div>
    </div>
</div>
