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

use core_payment\admin_setting_manage_payment_gateways;

/**
 * Admin setting to manage Shake-Out payment gateway.
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_manage_shakeout extends admin_setting_manage_payment_gateways {

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_name = 'paygw_shakeout';
        parent::__construct();
    }

    /**
     * Return the gateway name for display
     *
     * @return string
     */
    protected function get_gateway_name(): string {
        return get_string('gatewayname', 'paygw_shakeout');
    }

    /**
     * Return description of the gateway
     *
     * @return string
     */
    protected function get_gateway_description(): string {
        return get_string('gatewaydescription', 'paygw_shakeout');
    }
}
