/**
 * Mailable Admin Settings JavaScript
 * Handles dynamic driver switching and UI interactions
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        const $driverSelect = $('#mailable_active_driver');
        const $driverSettings = $('.mailable-driver-settings');

        // Function to show/hide driver settings
        function switchDriver(driverName) {
            // Hide all driver settings
            $driverSettings.each(function() {
                const $settings = $(this);
                const isActive = $settings.data('driver') === driverName;
                
                if (isActive) {
                    $settings.show();
                    // Add required attributes back to visible fields
                    $settings.find('input[data-required], select[data-required], textarea[data-required]').each(function() {
                        $(this).prop('required', true).removeAttr('data-required');
                    });
                } else {
                    $settings.hide();
                    // Remove required attributes from hidden fields to prevent browser validation errors
                    $settings.find('input[required], select[required], textarea[required]').each(function() {
                        $(this).attr('data-required', 'true').prop('required', false);
                    });
                }
            });
        }

        // Before form submit, ensure only active driver's required fields are required
        $('form[action="options.php"]').on('submit', function() {
            const activeDriver = $driverSelect.val();
            $driverSettings.each(function() {
                const $settings = $(this);
                const isActive = $settings.data('driver') === activeDriver;
                
                if (!isActive) {
                    // Remove required from all hidden fields
                    $settings.find('input[required], select[required], textarea[required]').each(function() {
                        $(this).prop('required', false);
                    });
                }
            });
        });

        // Handle driver selection change
        $driverSelect.on('change', function() {
            const selectedDriver = $(this).val();
            switchDriver(selectedDriver);
        });

        // Initialize on page load
        const initialDriver = $driverSelect.val();
        if (initialDriver) {
            switchDriver(initialDriver);
        }

        // Toggle Switch Functionality
        $('.mailable-toggle-switch').on('click', function() {
            const $toggle = $(this);
            const $checkbox = $('#' + $toggle.data('toggle-target'));
            const $label = $toggle.siblings().find('.mailable-toggle-label');
            
            const isActive = $toggle.hasClass('active');
            
            if (isActive) {
                $toggle.removeClass('active');
                $label.removeClass('active');
                $checkbox.prop('checked', false);
            } else {
                $toggle.addClass('active');
                $label.addClass('active');
                $checkbox.prop('checked', true);
            }
        });

        // Update form inputs to use new CSS classes
        $('.form-table input[type="text"], .form-table input[type="email"], .form-table input[type="password"]').each(function() {
            if (!$(this).hasClass('mailable-form-input')) {
                $(this).addClass('mailable-form-input');
            }
        });

        $('.form-table select').each(function() {
            if (!$(this).hasClass('mailable-form-select')) {
                $(this).addClass('mailable-form-select');
            }
        });

        $('.form-table textarea').each(function() {
            if (!$(this).hasClass('mailable-form-textarea')) {
                $(this).addClass('mailable-form-textarea');
            }
        });
    });

})(jQuery);
