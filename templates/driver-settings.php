<?php
/**
 * Driver Settings Template
 *
 * @package Mailable
 *
 * @var Mail_Driver    $active_driver
 * @var string         $active_driver_name
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<h2><?php echo esc_html( $active_driver->get_label() ); ?> Configuration</h2>
<table class="form-table">
    <?php
    $fields = $active_driver->get_settings_fields();
    foreach ( $fields as $field ) :
        ?>
        <tr valign="top">
            <th scope="row">
                <label for="mailable_<?php echo esc_attr( $active_driver_name ); ?>_<?php echo esc_attr( $field['key'] ); ?>">
                    <?php echo esc_html( $field['label'] ); ?>
                </label>
            </th>
            <td>
                <?php $active_driver->render_settings_field( $field ); ?>
            </td>
        </tr>
        <?php
    endforeach;
    ?>
</table>

