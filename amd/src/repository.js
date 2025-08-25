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
 * Shake-Out payment gateway repository for AJAX calls.
 *
 * @module     paygw_shakeout/repository
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax'], function(Ajax) {

    return {
        /**
         * Get payment status from Shake-Out API.
         *
         * @param {string} component
         * @param {string} paymentArea
         * @param {number} itemId
         * @param {string} invoiceId
         * @returns {Promise}
         */
        getPaymentStatus: function(component, paymentArea, itemId, invoiceId) {
            var request = {
                methodname: 'paygw_shakeout_get_payment_status',
                args: {
                    component: component,
                    paymentarea: paymentArea,
                    itemid: itemId,
                    invoiceid: invoiceId
                }
            };

            return Ajax.call([request])[0];
        },

        /**
         * Validate payment configuration.
         *
         * @param {Object} config
         * @returns {Promise}
         */
        validateConfig: function(config) {
            var request = {
                methodname: 'paygw_shakeout_validate_config',
                args: {
                    config: config
                }
            };

            return Ajax.call([request])[0];
        },

        /**
         * Create payment intent.
         *
         * @param {string} component
         * @param {string} paymentArea
         * @param {number} itemId
         * @param {string} description
         * @returns {Promise}
         */
        createPaymentIntent: function(component, paymentArea, itemId, description) {
            var request = {
                methodname: 'paygw_shakeout_create_payment_intent',
                args: {
                    component: component,
                    paymentarea: paymentArea,
                    itemid: itemId,
                    description: description
                }
            };

            return Ajax.call([request])[0];
        }
    };
});
