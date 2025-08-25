
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
 * Privacy provider for paygw_shakeout
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_shakeout\privacy;

use core_payment\privacy\paygw_provider;

class provider implements 
    \core_privacy\local\metadata\provider,
    paygw_provider {

    /**
     * Returns metadata about this plugin.
     *
     * @param \core_privacy\local\metadata\collection $collection
     * @return \core_privacy\local\metadata\collection
     */
    public static function get_metadata(\core_privacy\local\metadata\collection $collection): \core_privacy\local\metadata\collection {
        $collection->add_database_table('paygw_shakeout', [
            'paymentid' => 'privacy:metadata:paygw_shakeout:paymentid',
            'invoice_id' => 'privacy:metadata:paygw_shakeout:invoice_id',
            'invoice_ref' => 'privacy:metadata:paygw_shakeout:invoice_ref',
            'invoice_url' => 'privacy:metadata:paygw_shakeout:invoice_url',
            'status' => 'privacy:metadata:paygw_shakeout:status',
            'timecreated' => 'privacy:metadata:paygw_shakeout:timecreated',
            'timemodified' => 'privacy:metadata:paygw_shakeout:timemodified',
        ], 'privacy:metadata:paygw_shakeout');

        $collection->add_external_location_link('shakeout_api', [
            'firstname' => 'privacy:metadata:shakeout_api:firstname',
            'lastname' => 'privacy:metadata:shakeout_api:lastname',
            'email' => 'privacy:metadata:shakeout_api:email',
            'phone' => 'privacy:metadata:shakeout_api:phone',
            'address' => 'privacy:metadata:shakeout_api:address',
        ], 'privacy:metadata:shakeout_api');

        return $collection;
    }

    /**
     * Export all user data for the specified payment record.
     *
     * @param int $paymentid
     * @return \stdClass|null
     */
    public static function export_payment_data(int $paymentid): ?\stdClass {
        global $DB;
        
        $record = $DB->get_record('paygw_shakeout', ['paymentid' => $paymentid]);
        if (!$record) {
            return null;
        }

        return (object) [
            'invoice_id' => $record->invoice_id,
            'invoice_ref' => $record->invoice_ref,
            'status' => $record->status,
            'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
            'timemodified' => $record->timemodified ? \core_privacy\local\request\transform::datetime($record->timemodified) : null,
        ];
    }

    /**
     * Delete all user data for the specified payment record.
     *
     * @param int $paymentid
     */
    public static function delete_payment_data(int $paymentid): void {
        global $DB;
        $DB->delete_records('paygw_shakeout', ['paymentid' => $paymentid]);
    }
}
