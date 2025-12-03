<?php

/**
 * Driver Manager
 *
 * Manages mail service drivers
 *
 * @package Mailable
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
class Mail_Driver_Manager
{

    /**
     * Registered drivers
     *
     * @var array
     */
    private static $drivers = array();

    /**
     * Register a driver
     *
     * @param string $name Driver name/slug
     * @param string $class Driver class name
     * @return void
     */
    public static function register($name, $class)
    {
        if (class_exists($class) && is_subclass_of($class, 'Mail_Driver')) {
            self::$drivers[$name] = $class;
        }
    }

    /**
     * Get all registered drivers
     *
     * @return array
     */
    public static function get_drivers()
    {
        return self::$drivers;
    }

    /**
     * Get driver instance
     *
     * @param string $name Driver name
     * @return Mail_Driver|null
     */
    public static function get_driver($name)
    {
        if (! isset(self::$drivers[$name])) {
            return null;
        }

        $class = self::$drivers[$name];
        return new $class();
    }

    /**
     * Get active driver
     *
     * @return Mail_Driver|null
     */
    public static function get_active_driver()
    {
        $active_driver = get_option('mailable_active_driver', 'sendgrid');
        return self::get_driver($active_driver);
    }

    /**
     * Get driver options for select field
     *
     * @return array
     */
    public static function get_driver_options()
    {
        $options = array();

        foreach (self::$drivers as $name => $class) {
            $driver = new $class();
            $options[$name] = $driver->get_label();
        }

        return $options;
    }
}
