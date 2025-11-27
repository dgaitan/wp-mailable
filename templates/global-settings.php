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

<h2>Global Settings</h2>
<table class="form-table">
    <tr valign="top">
        <th scope="row">From Email</th>
        <td>
            <input
                type="email"
                name="<?php echo esc_attr( $option_from_email ); ?>"
                value="<?php echo esc_attr( get_option( $option_from_email ) ); ?>"
                class="regular-text" />
            <p class="description">Global "From" email address (optional). Can be overridden by driver settings.</p>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row">From Name</th>
        <td>
            <input
                type="text"
                name="<?php echo esc_attr( $option_from_name ); ?>"
                value="<?php echo esc_attr( get_option( $option_from_name ) ); ?>"
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
                    name="<?php echo esc_attr( $option_force_from ); ?>"
                    value="1"
                    <?php checked( 1, get_option( $option_force_from ) ); ?> />
                Force all emails to use the "From" values above.
            </label>
            <p class="description">Recommended. Prevents other plugins from setting their own "From" headers.</p>
        </td>
    </tr>
</table>

