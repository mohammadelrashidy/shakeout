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
 * @package    paygw_shakeout
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $mform->addRule('apikey', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'secretkey', get_string('secretkey', 'paygw_shakeout'));
        $mform->setType('secretkey', PARAM_TEXT);
        $mform->addHelpButton('secretkey', 'secretkey', 'paygw_shakeout');
        $mform->addRule('secretkey', get_string('required'), 'required', null, 'client');

        $mform->addElement('selectyesno', 'sandbox', get_string('sandbox', 'paygw_shakeout'));
        $mform->setDefault('sandbox', 1);
        $mform->addHelpButton('sandbox', 'sandbox', 'paygw_shakeout');

        // API Endpoint validation
        $mform->addElement('text', 'apibaseurl', get_string('apibaseurl', 'paygw_shakeout'));
        $mform->setType('apibaseurl', PARAM_URL);
        $mform->setDefault('apibaseurl', 'https://api.shake-out.com/api/v1/');
        $mform->addHelpButton('apibaseurl', 'apibaseurl', 'paygw_shakeout');

        $mform->addElement('text', 'sandboxapibaseurl', get_string('sandboxapibaseurl', 'paygw_shakeout'));
        $mform->setType('sandboxapibaseurl', PARAM_URL);
        $mform->setDefault('sandboxapibaseurl', 'https://sandbox-api.shake-out.com/api/v1/');
        $mform->addHelpButton('sandboxapibaseurl', 'sandboxapibaseurl', 'paygw_shakeout');

        $mform->addElement('text', 'successurl', get_string('successurl', 'paygw_shakeout'));
        $mform->setType('successurl', PARAM_URL);
        $mform->addHelpButton('successurl', 'successurl', 'paygw_shakeout');

        $mform->addElement('text', 'failureurl', get_string('failureurl', 'paygw_shakeout'));
        $mform->setType('failureurl', PARAM_URL);
        $mform->addHelpButton('failureurl', 'failureurl', 'paygw_shakeout');

        $mform->addElement('text', 'pendingurl', get_string('pendingurl', 'paygw_shakeout'));
        $mform->setType('pendingurl', PARAM_URL);
        $mform->addHelpButton('pendingurl', 'pendingurl', 'paygw_shakeout');

        // Connection test button
        $mform->addElement('button', 'testconnection', get_string('testconnection', 'paygw_shakeout'));
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form, 
                                               \stdClass $data, array $files, array &$errors): void {
        if (empty($data->apikey)) {
            $errors['apikey'] = get_string('gatewaycannotbeenabled', 'paygw_shakeout');
        }

        if (empty($data->secretkey)) {
            $errors['secretkey'] = get_string('gatewaycannotbeenabled', 'paygw_shakeout');
        }

        // Test API connectivity if credentials are provided
        if (!empty($data->apikey) && !empty($data->secretkey)) {
            $sandbox = !empty($data->sandbox);
            $apibaseurl = $sandbox ? 
                (!empty($data->sandboxapibaseurl) ? $data->sandboxapibaseurl : 'https://sandbox-api.shake-out.com/api/v1/') :
                (!empty($data->apibaseurl) ? $data->apibaseurl : 'https://api.shake-out.com/api/v1/');

            try {
                $connectivity_result = shakeout_helper::test_api_connectivity($data->apikey, $apibaseurl);
                if (!$connectivity_result['success']) {
                    $errors['apikey'] = get_string('apiconnectionfailed', 'paygw_shakeout', $connectivity_result['error']);
                }
            } catch (Exception $e) {
                $errors['apikey'] = get_string('apiconnectionfailed', 'paygw_shakeout', $e->getMessage());
            }
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
    public static function get_paymentarea_payment_ui(\stdClass $config, 
                                                    \core_payment\local\entities\payable $payable, 
                                                    string $component, string $paymentarea, 
                                                    int $itemid): string {
        global $CFG, $PAGE;

        // Validate configuration before proceeding
        if (empty($config->apikey) || empty($config->secretkey)) {
            return '<div class="alert alert-danger">' . 
                   get_string('gatewaynotconfigured', 'paygw_shakeout') . 
                   '</div>';
        }

        // Test API connectivity
        $sandbox = !empty($config->sandbox);
        $apibaseurl = $sandbox ? 
            (!empty($config->sandboxapibaseurl) ? $config->sandboxapibaseurl : 'https://sandbox-api.shake-out.com/api/v1/') :
            (!empty($config->apibaseurl) ? $config->apibaseurl : 'https://api.shake-out.com/api/v1/');

        try {
            $connectivity_result = shakeout_helper::test_api_connectivity($config->apikey, $apibaseurl);
            if (!$connectivity_result['success']) {
                return '<div class="alert alert-danger">' . 
                       get_string('apiconnectionfailed', 'paygw_shakeout', $connectivity_result['error']) . 
                       '</div>';
            }
        } catch (Exception $e) {
            return '<div class="alert alert-danger">' . 
                   get_string('apiconnectionfailed', 'paygw_shakeout', $e->getMessage()) . 
                   '</div>';
        }

        // Build payment URL
        $payurl = $CFG->wwwroot . '/payment/gateway/shakeout/pay.php';
        $params = [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'description' => $payable->get_description()
        ];

        // Create unique payment identifier for this session
        $paymenttoken = uniqid('shakeout_', true);
        $params['token'] = $paymenttoken;
        
        $payurl .= '?' . http_build_query($params);

        // Load the enhanced JavaScript module
        $PAGE->requires->js_call_amd('paygw_shakeout/payment_handler', 'init', [
            'paymentUrl' => $payurl,
            'confirmMessage' => get_string('paymentconfirm', 'paygw_shakeout'),
            'processingMessage' => get_string('processing', 'paygw_shakeout'),
            'errorMessage' => get_string('paymentprocessingerror', 'paygw_shakeout')
        ]);

        // Create payment form with improved modal handling
        $html = '<div class="shakeout-payment-gateway">';
        $html .= '<div class="card">';
        $html .= '<div class="card-body text-center">';
        $html .= '<h5 class="card-title">' . get_string('paywitshakeout', 'paygw_shakeout') . '</h5>';
        $html .= '<p class="card-text">' . get_string('gatewaydescription', 'paygw_shakeout') . '</p>';
        
        // Amount display
        $amount = \core_payment\helper::get_rounded_cost(
            $payable->get_amount(), 
            $payable->get_currency(), 
            \core_payment\helper::get_gateway_surcharge('shakeout')
        );
        $html .= '<p class="h6 text-muted">Amount: ' . 
                 shakeout_helper::format_amount($amount, $payable->get_currency()) . '</p>';
        
        // Payment button with confirmation
        $html .= '<button type="button" class="btn btn-primary btn-lg btn-shakeout-pay" id="shakeout-pay-btn">';
        $html .= '<i class="fa fa-credit-card" aria-hidden="true"></i> ';
        $html .= get_string('paybutton', 'paygw_shakeout');
        $html .= '</button>';
        
        // Loading indicator
        $html .= '<div id="shakeout-loading" class="mt-3" style="display: none;">';
        $html .= '<div class="spinner-border text-primary" role="status">';
        $html .= '<span class="sr-only">' . get_string('processing', 'paygw_shakeout') . '</span>';
        $html .= '</div>';
        $html .= '<p class="mt-2">' . get_string('processing', 'paygw_shakeout') . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
