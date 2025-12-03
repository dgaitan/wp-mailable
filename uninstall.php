<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Mailable
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
$mailable_options = array(
    'mailable_active_driver',
    'mailable_from_email',
    'mailable_from_name',
    'mailable_force_from',
);

// Get all registered drivers and delete their options
require_once plugin_dir_path(__FILE__) . 'includes/class-driver-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mail-driver.php';
require_once plugin_dir_path(__FILE__) . 'includes/drivers/class-sendgrid-driver.php';
require_once plugin_dir_path(__FILE__) . 'includes/drivers/class-mailpit-driver.php';

// Register drivers to get their settings
$mailable_sendgrid = new SendGrid_Driver();
$mailable_mailpit = new Mailpit_Driver();

$mailable_drivers = array('sendgrid' => $mailable_sendgrid, 'mailpit' => $mailable_mailpit);

// Allow other plugins to register drivers before cleanup
do_action('mailable_register_drivers');

// Get all registered drivers
$mailable_all_drivers = Mail_Driver_Manager::get_drivers();

foreach ($mailable_all_drivers as $mailable_driver_name => $mailable_driver_class) {
    $mailable_driver = new $mailable_driver_class();
    $mailable_fields = $mailable_driver->get_settings_fields();

    foreach ($mailable_fields as $mailable_field) {
        $mailable_option_key = 'mailable_' . $mailable_driver_name . '_' . $mailable_field['key'];
        delete_option($mailable_option_key);
    }
}

// Delete main plugin options
foreach ($mailable_options as $mailable_option) {
    delete_option($mailable_option);
}

