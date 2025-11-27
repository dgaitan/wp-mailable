/**
 * Mailable Admin Settings JavaScript
 * Handles dynamic driver switching
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
    });

})(jQuery);

