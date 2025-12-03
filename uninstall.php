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
$options = array(
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
$sendgrid = new SendGrid_Driver();
$mailpit = new Mailpit_Driver();

$drivers = array('sendgrid' => $sendgrid, 'mailpit' => $mailpit);

// Allow other plugins to register drivers before cleanup
do_action('mailable_register_drivers');

// Get all registered drivers
$all_drivers = Mail_Driver_Manager::get_drivers();

foreach ($all_drivers as $driver_name => $driver_class) {
    $driver = new $driver_class();
    $fields = $driver->get_settings_fields();

    foreach ($fields as $field) {
        $option_key = 'mailable_' . $driver_name . '_' . $field['key'];
        delete_option($option_key);
    }
}

// Delete main plugin options
foreach ($options as $option) {
    delete_option($option);
}

