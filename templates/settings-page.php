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

// Get current tab from URL or default to 'settings'
$mailable_current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';
?>

<div class="mailable-admin-wrap">
    <!-- Sidebar Navigation -->
    <aside class="mailable-sidebar">
        <div class="mailable-sidebar-header">
            <h1 class="mailable-logo">MAILABLE</h1>
            <p class="mailable-tagline"><?php esc_html_e('Flexible WordPress Email Delivery', 'mailable'); ?></p>
        </div>
        <nav class="mailable-nav">
            <ul class="mailable-nav">
                <li class="mailable-nav-item">
                    <a href="<?php echo esc_url( admin_url( 'options-general.php?page=mailable-settings&tab=settings' ) ); ?>" 
                       class="mailable-nav-link <?php echo $mailable_current_tab === 'settings' ? 'active' : ''; ?>">
                        <svg class="mailable-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="mailable-nav-text">
                            <?php esc_html_e('Settings', 'mailable'); ?>
                            <span class="mailable-nav-desc"><?php esc_html_e('Configure email provider', 'mailable'); ?></span>
                        </span>
                    </a>
                </li>
                <li class="mailable-nav-item">
                    <a href="<?php echo esc_url( admin_url( 'options-general.php?page=mailable-settings&tab=test' ) ); ?>" 
                       class="mailable-nav-link <?php echo $mailable_current_tab === 'test' ? 'active' : ''; ?>">
                        <svg class="mailable-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="mailable-nav-text">
                            <?php esc_html_e('Test Email', 'mailable'); ?>
                            <span class="mailable-nav-desc"><?php esc_html_e('Test connection & send', 'mailable'); ?></span>
                        </span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="mailable-content">
        <?php if ( $mailable_current_tab === 'settings' ) : ?>
            <!-- Settings Tab -->
            <div class="mailable-content-header">
                <svg class="mailable-content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h2 class="mailable-content-title"><?php esc_html_e('Settings', 'mailable'); ?></h2>
            </div>

            <form action="options.php" method="post">
                <?php settings_fields( 'mailable_settings_group' ); ?>

                <!-- Provider Selection Card -->
                <div class="mailable-card">
                    <div class="mailable-card-header">
                        <h3 class="mailable-card-title">
                            <svg class="mailable-card-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <?php esc_html_e('Mail Service Provider', 'mailable'); ?>
                        </h3>
                    </div>
                    <div class="mailable-form-group">
                        <label for="mailable_active_driver" class="mailable-form-label"><?php esc_html_e('Select Provider', 'mailable'); ?></label>
                        <select name="<?php echo esc_attr( $option_active_driver ); ?>" id="mailable_active_driver" class="mailable-form-select">
                            <?php foreach ( $available_drivers as $mailable_name => $mailable_label ) : ?>
                                <option value="<?php echo esc_attr( $mailable_name ); ?>" <?php selected( $active_driver_name, $mailable_name ); ?>>
                                    <?php echo esc_html( $mailable_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mailable-form-description"><?php esc_html_e('Choose your email service provider. Each provider has different configuration requirements.', 'mailable'); ?></p>
                    </div>
                </div>

                <!-- Driver Settings (dynamically shown based on selection) -->
                <?php
                $mailable_drivers = Mail_Driver_Manager::get_drivers();
                foreach ( $mailable_drivers as $mailable_driver_name => $mailable_driver_class ) :
                    $mailable_driver = new $mailable_driver_class();
                    $mailable_is_active = ( $mailable_driver_name === $active_driver_name );
                    ?>
                    <div class="mailable-driver-settings mailable-card" data-driver="<?php echo esc_attr( $mailable_driver_name ); ?>" style="<?php echo $mailable_is_active ? '' : 'display: none;'; ?>">
                        <div class="mailable-card-header">
                            <h3 class="mailable-card-title">
                                <svg class="mailable-card-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <?php echo esc_html( $mailable_driver->get_label() ); ?> <?php esc_html_e('Configuration', 'mailable'); ?>
                            </h3>
                        </div>
                        <?php
                        $mailable_fields = $mailable_driver->get_settings_fields();
                        foreach ( $mailable_fields as $mailable_field ) :
                            ?>
                            <div class="mailable-form-group">
                                <label for="mailable_<?php echo esc_attr( $mailable_driver_name ); ?>_<?php echo esc_attr( $mailable_field['key'] ); ?>" 
                                       class="mailable-form-label <?php echo isset( $mailable_field['required'] ) && $mailable_field['required'] ? 'required' : ''; ?>">
                                    <?php echo esc_html( $mailable_field['label'] ); ?>
                                </label>
                                <?php $mailable_driver->render_settings_field( $mailable_field ); ?>
                                <?php if ( isset( $mailable_field['description'] ) ) : ?>
                                    <p class="mailable-form-description"><?php echo esc_html( $mailable_field['description'] ); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php
                        endforeach;
                        ?>
                    </div>
                <?php endforeach; ?>

                <?php require MAILABLE_PLUGIN_DIR . 'templates/global-settings.php'; ?>

                <div class="mailable-card">
                    <p>
                        <button type="submit" class="mailable-button mailable-button-primary">
                            <svg class="mailable-button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <?php esc_html_e('Save Changes', 'mailable'); ?>
                        </button>
                    </p>
                </div>
            </form>

        <?php elseif ( $mailable_current_tab === 'test' ) : ?>
            <!-- Test Email Tab -->
            <div class="mailable-content-header">
                <svg class="mailable-content-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h2 class="mailable-content-title"><?php esc_html_e('Test Email', 'mailable'); ?></h2>
            </div>

            <?php require MAILABLE_PLUGIN_DIR . 'templates/test-email.php'; ?>

        <?php endif; ?>
    </main>
</div>
