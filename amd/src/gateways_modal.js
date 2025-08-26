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
 * Shake-Out payment gateway modal handler
 *
 * @module     paygw_shakeout/gateways_modal
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/str', 'core/ajax'], 
function($, ModalFactory, ModalEvents, Str, Ajax) {
    'use strict';

    /**
     * Initialise the payment process for Shake-Out gateway
     *
     * @param {String} component The component name
     * @param {String} paymentArea The payment area
     * @param {Number} itemId The item ID
     * @param {String} description The payment description
     * @returns {Promise}
     */
    var process = function(component, paymentArea, itemId, description) {
        return new Promise(function(resolve, reject) {
            var modalPromise = ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: Str.get_string('paymentconfirm', 'paygw_shakeout'),
                body: Str.get_string('paymentconfirmdesc', 'paygw_shakeout')
            });

            modalPromise.then(function(modal) {
                // Add custom footer with payment buttons
                modal.setFooter(
                    '<button type="button" class="btn btn-primary" data-action="pay">' +
                    'Pay Now</button>' +
                    '<button type="button" class="btn btn-secondary" data-action="cancel">' +
                    'Cancel</button>'
                );

                // Handle pay button click
                modal.getRoot().on('click', '[data-action="pay"]', function() {
                    modal.hide();
                    
                    // Build payment URL
                    var paymentUrl = M.cfg.wwwroot + '/payment/gateway/shakeout/pay.php';
                    var params = new URLSearchParams({
                        component: component,
                        paymentarea: paymentArea,
                        itemid: itemId,
                        description: description,
                        sesskey: M.cfg.sesskey
                    });
                    
                    // Open payment gateway in new window/tab
                    var paymentWindow = window.open(
                        paymentUrl + '?' + params.toString(),
                        'shakeout_payment',
                        'width=800,height=600,scrollbars=yes,resizable=yes'
                    );
                    
                    // Focus the payment window
                    if (paymentWindow) {
                        paymentWindow.focus();
                    }
                    
                    resolve();
                });

                // Handle cancel button click
                modal.getRoot().on('click', '[data-action="cancel"]', function() {
                    modal.hide();
                    reject(new Error('Payment cancelled by user'));
                });

                // Show the modal
                modal.show();
                return modal;
            }).catch(function(error) {
                console.error('Shake-Out: Error creating payment modal:', error);
                reject(error);
            });
        });
    };

    return {
        process: process
    };
});