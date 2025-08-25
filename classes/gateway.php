<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace paygw_shakeout;

use core_payment\local\entities\payable;

/**
 * The gateway class for Shake-Out payment gateway plugin.
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gateway extends \core_payment\gateway {

    /**
     * Configuration form for the gateway instance
     *
     * This method should return list of the configuration fields that should be
     * displayed in the configuration form.
     *
     * @return string[]
     */
    public static function get_supported_currencies(): array {
        // Shake-Out supports multiple currencies
        return [
            'USD', 'EUR', 'GBP', 'EGP', 'SAR', 'AED', 'KWD', 'QAR', 'BHD', 'OMR', 'JOD'
        ];
    }

    /**
     * Configuration form for the gateway instance
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'apikey', get_string('apikey', 'paygw_shakeout'));
        $mform->setType('apikey', PARAM_TEXT);
        $mform->addHelpButton('apikey', 'apikey', 'paygw_shakeout');

        $mform->addElement('text', 'secretkey', get_string('secretkey', 'paygw_shakeout'));
        $mform->setType('secretkey', PARAM_TEXT);
        $mform->addHelpButton('secretkey', 'secretkey', 'paygw_shakeout');

        $mform->addElement('selectyesno', 'sandbox', get_string('sandbox', 'paygw_shakeout'));
        $mform->setDefault('sandbox', 1);
        $mform->addHelpButton('sandbox', 'sandbox', 'paygw_shakeout');

        $mform->addElement('text', 'successurl', get_string('successurl', 'paygw_shakeout'));
        $mform->setType('successurl', PARAM_URL);
        $mform->addHelpButton('successurl', 'successurl', 'paygw_shakeout');

        $mform->addElement('text', 'failureurl', get_string('failureurl', 'paygw_shakeout'));
        $mform->setType('failureurl', PARAM_URL);
        $mform->addHelpButton('failureurl', 'failureurl', 'paygw_shakeout');

        $mform->addElement('text', 'pendingurl', get_string('pendingurl', 'paygw_shakeout'));
        $mform->setType('pendingurl', PARAM_URL);
        $mform->addHelpButton('pendingurl', 'pendingurl', 'paygw_shakeout');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form, \stdClass $data, array $files, array &$errors): void {
        if (empty($data->apikey)) {
            $errors['apikey'] = get_string('gatewaycannotbeenabled', 'paygw_shakeout');
        }
        if (empty($data->secretkey)) {
            $errors['secretkey'] = get_string('gatewaycannotbeenabled', 'paygw_shakeout');
        }
    }

    /**
     * Checks if the gateway can be used for payments.
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @return bool
     */
    public static function can_use_internal_gateways(): bool {
        return true;
    }

    /**
     * Return the list of countries that the gateway supports payments from.
     *
     * @return string[] An array of ISO 3166-1 alpha-2 country codes.
     */
    public static function get_supported_countries(): array {
        return ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'US', 'GB', 'EU'];
    }

    /**
     * Returns the payment form UI that should be displayed to the user when they choose to pay with this gateway.
     *
     * @param \stdClass $config Gateway configuration 
     * @param \core_payment\local\entities\payable $payable Information about the payment
     * @param string $component Name of the component that the itemid belongs to
     * @param string $paymentarea Name of the payment area
     * @param int $itemid An identifier that is known to the component
     * @return string HTML content to display in the payment area
     */
    public static function get_paymentarea_payment_ui(\stdClass $config, \core_payment\local\entities\payable $payable, string $component, string $paymentarea, int $itemid): string {
        global $CFG, $PAGE;
        
        // Build payment URL
        $payurl = $CFG->wwwroot . '/payment/gateway/shakeout/pay.php';
        $params = [
            'component' => $component,
            'paymentarea' => $paymentarea, 
            'itemid' => $itemid,
            'description' => $payable->get_description()
        ];
        
        $payurl .= '?' . http_build_query($params);
        
        // Add JavaScript for direct redirect (no modal)
        $PAGE->requires->js_amd_inline('
            require(["jquery"], function($) {
                $(document).ready(function() {
                    $(".btn-shakeout-pay").on("click", function(e) {
                        e.preventDefault();
                        $(this).prop("disabled", true).html("Processing...");
                        window.location.href = "' . $payurl . '";
                    });
                });
            });
        ');
        
        // Create payment form
        $html = '<div class="shakeout-payment-gateway">';
        $html .= '<div class="card">';
        $html .= '<div class="card-body text-center">';
        $html .= '<h5 class="card-title">' . get_string('paywitshakeout', 'paygw_shakeout') . '</h5>';
        $html .= '<p class="card-text">' . get_string('gatewaydescription', 'paygw_shakeout') . '</p>';
        $html .= '<button type="button" class="btn btn-primary btn-lg btn-shakeout-pay">';
        $html .= '<i class="fa fa-credit-card" aria-hidden="true"></i> ';
        $html .= get_string('paybutton', 'paygw_shakeout');
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}
