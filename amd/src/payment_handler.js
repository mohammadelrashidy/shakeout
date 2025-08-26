// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Shake-Out payment handler for modal and payment processing
 *
 * @module     paygw_shakeout/payment_handler
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str'], 
function($, ModalFactory, ModalEvents, Str) {
    'use strict';

    /**
     * Initialize the payment handler
     *
     * @param {Object} config Configuration object
     */
    function init(config) {
        // Ensure DOM is ready
        $(document).ready(function() {
            setupPaymentHandler(config);
        });
    }

    /**
     * Setup the payment button handler
     *
     * @param {Object} config Configuration object
     */
    function setupPaymentHandler(config) {
        var payButton = $('#shakeout-pay-btn');
        var loadingDiv = $('#shakeout-loading');

        if (payButton.length === 0) {
            console.error('Shake-Out: Payment button not found');
            return;
        }

        // Remove any existing event handlers
        payButton.off('click.shakeout');

        // Add new event handler with namespace
        payButton.on('click.shakeout', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Prevent double-clicks
            if (payButton.prop('disabled')) {
                return;
            }

            handlePaymentClick(config, payButton, loadingDiv);
        });
    }

    /**
     * Handle payment button click
     *
     * @param {Object} config Configuration object
     * @param {jQuery} payButton Payment button element
     * @param {jQuery} loadingDiv Loading indicator element
     */
    function handlePaymentClick(config, payButton, loadingDiv) {
        // Show confirmation modal first
        showConfirmationModal(config, function() {
            // User confirmed, proceed with payment
            processPayment(config, payButton, loadingDiv);
        });
    }

    /**
     * Show payment confirmation modal
     *
     * @param {Object} config Configuration object
     * @param {Function} callback Callback to execute on confirmation
     */
    function showConfirmationModal(config, callback) {
        var modalPromise = ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: Str.get_string('paymentconfirm', 'paygw_shakeout'),
            body: config.confirmMessage || 'Are you sure you want to proceed with this payment?',
            removeOnClose: false // Prevent modal from being removed immediately
        });

        modalPromise.then(function(modal) {
            // Prevent modal from closing automatically
            modal.getRoot().on('hidden.bs.modal', function(e) {
                e.stopPropagation();
            });

            // Handle the save event (user clicked confirm)
            modal.getRoot().on(ModalEvents.save, function(e) {
                e.preventDefault();
                e.stopPropagation();
                modal.hide();
                // Small delay to ensure modal closes properly before callback
                setTimeout(function() {
                    modal.destroy();
                    callback();
                }, 100);
            });

            // Handle cancel event
            modal.getRoot().on(ModalEvents.cancel, function(e) {
                e.preventDefault();
                e.stopPropagation();
                modal.hide();
                setTimeout(function() {
                    modal.destroy();
                }, 100);
            });

            // Show the modal with proper focus
            modal.show();
            
            // Ensure modal stays open and focused
            setTimeout(function() {
                modal.getRoot().find('.btn-primary').focus();
            }, 200);
            
            return modal;
        }).catch(function(error) {
            console.error('Shake-Out: Error creating confirmation modal:', error);
            // Fallback to browser confirm
            if (confirm(config.confirmMessage || 'Are you sure you want to proceed with this payment?')) {
                callback();
            }
        });
    }

    /**
     * Process the payment
     *
     * @param {Object} config Configuration object
     * @param {jQuery} payButton Payment button element
     * @param {jQuery} loadingDiv Loading indicator element
     */
    function processPayment(config, payButton, loadingDiv) {
        // Disable the button and show loading
        payButton.prop('disabled', true);
        payButton.html('<i class="fa fa-spinner fa-spin"></i> ' + 
                      (config.processingMessage || 'Processing...'));
        
        if (loadingDiv.length > 0) {
            loadingDiv.show();
        }

        // Add a small delay to ensure the user sees the processing state
        setTimeout(function() {
            // Test if the payment URL is reachable
            testPaymentUrl(config.paymentUrl).then(function(isReachable) {
                if (isReachable) {
                    // Redirect to payment gateway
                    window.location.href = config.paymentUrl;
                } else {
                    // Show error and re-enable button
                    showPaymentError(config, payButton, loadingDiv);
                }
            });
        }, 500);
    }

    /**
     * Test if payment URL is reachable
     *
     * @param {String} url Payment URL
     * @return {Promise} Promise that resolves to boolean
     */
    function testPaymentUrl(url) {
        return new Promise(function(resolve) {
            // Create a test request to check if the payment endpoint is reachable
            var xhr = new XMLHttpRequest();
            
            xhr.timeout = 5000; // 5 second timeout
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Consider any response (even error responses) as reachable
                    // since the payment URL might return errors for GET requests
                    resolve(xhr.status !== 0);
                }
            };
            
            xhr.ontimeout = function() {
                resolve(false);
            };
            
            xhr.onerror = function() {
                resolve(false);
            };
            
            try {
                xhr.open('HEAD', url, true);
                xhr.send();
            } catch (e) {
                resolve(false);
            }
        });
    }

    /**
     * Show payment error
     *
     * @param {Object} config Configuration object
     * @param {jQuery} payButton Payment button element
     * @param {jQuery} loadingDiv Loading indicator element
     */
    function showPaymentError(config, payButton, loadingDiv) {
        // Hide loading
        if (loadingDiv.length > 0) {
            loadingDiv.hide();
        }

        // Re-enable button
        payButton.prop('disabled', false);
        payButton.html('<i class="fa fa-credit-card"></i> Pay now');

        // Show error modal
        var errorMessage = config.errorMessage || 
                          'Payment gateway is currently unavailable. Please try again later or contact support.';
        
        var modalPromise = ModalFactory.create({
            type: ModalFactory.types.ALERT,
            title: Str.get_string('error', 'core'),
            body: errorMessage
        });

        modalPromise.then(function(modal) {
            modal.show();
            return modal;
        }).catch(function(error) {
            console.error('Shake-Out: Error showing error modal:', error);
            // Fallback to alert
            alert(errorMessage);
        });
    }

    // Public API
    return {
        init: init
    };
});
