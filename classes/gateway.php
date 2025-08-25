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

/**
 * Shake-Out payment gateway class
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_shakeout;

use core_payment\helper;
use core_payment\form\account_gateway;

class gateway extends \core_payment\gateway {
    
    /**
     * The configuration form fields
     *
     * @return array
     */
    public static function get_supported_currencies(): array {
        return [
            'EGP', 'USD', 'EUR', 'GBP'
        ];
    }

    /**
     * Configuration form for the gateway
     *
     * @param \MoodleQuickForm $mform
     * @param array $data
     * @param int $accountid
     */
    public static function add_configuration_to_gateway_form(\MoodleQuickForm $mform, array $data = [], $accountid = null) {
        $mform->addElement('text', 'apikey', get_string('apikey', 'paygw_shakeout'));
        $mform->setType('apikey', PARAM_TEXT);
        $mform->addHelpButton('apikey', 'apikey', 'paygw_shakeout');

        $mform->addElement('text', 'secretkey', get_string('secretkey', 'paygw_shakeout'));
        $mform->setType('secretkey', PARAM_TEXT);
        $mform->addHelpButton('secretkey', 'secretkey', 'paygw_shakeout');

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
     * Validates the gateway configuration
     *
     * @param \MoodleQuickForm $mform
     * @param array $data
     * @param array $files
     * @param array $errors
     * @return array
     */
    public static function validate_gateway_form(\MoodleQuickForm $mform, array $data, array $files, array &$errors) {
        if (empty($data['apikey'])) {
            $errors['apikey'] = get_string('gatewaycannotbeenabled', 'paygw_shakeout');
        }
        if (empty($data['secretkey'])) {
            $errors['secretkey'] = get_string('gatewaycannotbeenabled', 'paygw_shakeout');
        }
    }

    /**
     * Returns the list of currencies that the gateway supports.
     *
     * @return string[]
     */

}

