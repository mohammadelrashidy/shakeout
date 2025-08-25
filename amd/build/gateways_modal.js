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
 * Shake-Out payment gateway modal functionality.
 *
 * @module     paygw_shakeout/gateways_modal
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/str',
    'core/templates',
    'core/modal_factory',
    'core/modal_events',
    'core/notification'
], function(Str, Templates, ModalFactory, ModalEvents, Notification) {

    /**
     * Process the payment by redirecting to the payment page.
     *
     * @param {string} component
     * @param {string} paymentArea
     * @param {number} itemId
     * @param {string} description
     */
    var processPayment = function(component, paymentArea, itemId, description) {
        // Show processing notification.
        Str.get_string('processing', 'paygw_shakeout').then(function(processingString) {
            Notification.add({
                message: processingString,
                type: 'info'
            });
            return null;
        }).catch(function() {
            // Fallback message if string loading fails.
            Notification.add({
                message: 'Processing payment...',
                type: 'info'
            });
        });

        // Build payment URL.
        var paymentUrl = new URL(M.cfg.wwwroot + '/payment/gateway/shakeout/pay.php');
        paymentUrl.searchParams.append('component', component);
        paymentUrl.searchParams.append('paymentarea', paymentArea);
        paymentUrl.searchParams.append('itemid', itemId);
        paymentUrl.searchParams.append('description', description);

        // Redirect to payment page.
        window.location.href = paymentUrl.toString();
    };

    return {
        /**
         * Creates and shows the payment modal for Shake-Out gateway.
         *
         * @param {string} component
         * @param {string} paymentArea
         * @param {number} itemId
         * @param {string} description
         * @returns {Promise}
         */
        process: function(component, paymentArea, itemId, description) {
            return ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: Str.get_string('paywitshakeout', 'paygw_shakeout'),
                body: Templates.render('paygw_shakeout/shakeout_button', {
                    component: component,
                    paymentarea: paymentArea,
                    itemid: itemId,
                    description: description
                })
            }).then(function(modal) {
                // Handle form submission.
                modal.getRoot().on(ModalEvents.save, function() {
                    processPayment(component, paymentArea, itemId, description);
                });

                modal.show();
                return modal;
            });
        },

        /**
         * Initialise the payment process.
         *
         * @param {string} component
         * @param {string} paymentArea
         * @param {number} itemId
         * @param {string} description
         */
        init: function(component, paymentArea, itemId, description) {
            // Add click handler to pay button if it exists.
            var payButton = document.querySelector('[data-action="pay-shakeout"]');
            if (payButton) {
                payButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    processPayment(component, paymentArea, itemId, description);
                });
            }
        }
    };
});
