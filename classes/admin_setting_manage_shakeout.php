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

/**
 * Admin settings management for Shake-Out gateway
 *
 * @package    paygw_shakeout
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_manage_shakeout extends \admin_setting {

    /**
     * Constructor
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('paygw_shakeout_manage', 
                          get_string('manageshakeout', 'paygw_shakeout'), 
                          get_string('manageshakeout_desc', 'paygw_shakeout'), 
                          '');
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing
     *
     * @param mixed $data
     * @return true
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Returns an XHTML string for the setting
     *
     * @param mixed $data
     * @param string $query
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query = '') {
        global $CFG, $OUTPUT;

        $html = '';
        $html .= '<div class="form-item">';
        $html .= '<div class="form-label">';
        $html .= '<label>' . $this->visiblename . '</label>';
        $html .= '</div>';
        $html .= '<div class="form-setting">';
        $html .= '<div class="form-defaultinfo">' . $this->description . '</div>';
        
        // Add management links
        $manageurl = new \moodle_url('/admin/settings.php', ['section' => 'paymentgateway_shakeout']);
        $html .= '<p><a href="' . $manageurl . '" class="btn btn-secondary">' . 
                 get_string('managegateways', 'core_payment') . '</a></p>';
        
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
