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
 * Plugin administration pages are defined here.
 *
 * @package    paygw_shakeout
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Add global settings for the plugin if needed
    $settings->add(new admin_setting_heading('paygw_shakeout/general',
        get_string('pluginname', 'paygw_shakeout'),
        get_string('pluginname_desc', 'paygw_shakeout')
    ));

    $settings->add(new admin_setting_configcheckbox('paygw_shakeout/debug',
        get_string('debugmode', 'paygw_shakeout'),
        get_string('debugmode_desc', 'paygw_shakeout'),
        0
    ));
}
