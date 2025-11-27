<?php
/**
 * Settings Page Template
 *
 * @package Mailable
 *
 * @var string              $active_driver_name
 * @var Mail_Driver|null    $active_driver
 * @var array               $available_drivers
 * @var string              $option_active_driver
 * @var string              $option_from_email
 * @var string              $option_from_name
 * @var string              $option_force_from
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1>Mailable Settings</h1>
    <p>Configure your email service provider to send emails through WordPress.</p>

    <form action="options.php" method="post">
        <?php settings_fields( 'mailable_settings_group' ); ?>

        <h2>Mail Service Provider</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="mailable_active_driver">Select Provider</label>
                </th>
                <td>
                    <select name="<?php echo esc_attr( $option_active_driver ); ?>" id="mailable_active_driver">
                        <?php foreach ( $available_drivers as $name => $label ) : ?>
                            <option value="<?php echo esc_attr( $name ); ?>" <?php selected( $active_driver_name, $name ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Choose your email service provider.</p>
                </td>
            </tr>
        </table>

        <?php if ( $active_driver ) : ?>
            <?php require MAILABLE_PLUGIN_DIR . 'templates/driver-settings.php'; ?>
        <?php endif; ?>

        <?php require MAILABLE_PLUGIN_DIR . 'templates/global-settings.php'; ?>

        <?php submit_button(); ?>
    </form>

    <hr>

    <?php require MAILABLE_PLUGIN_DIR . 'templates/test-email.php'; ?>
</div>

