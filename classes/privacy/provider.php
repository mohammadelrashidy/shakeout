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

namespace paygw_shakeout\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy subsystem implementation for paygw_shakeout.
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements 
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'paygw_shakeout',
            [
                'paymentid' => 'privacy:metadata:paygw_shakeout:paymentid',
                'invoice_id' => 'privacy:metadata:paygw_shakeout:invoice_id',
                'invoice_ref' => 'privacy:metadata:paygw_shakeout:invoice_ref',
                'invoice_url' => 'privacy:metadata:paygw_shakeout:invoice_url',
                'status' => 'privacy:metadata:paygw_shakeout:status',
                'timecreated' => 'privacy:metadata:paygw_shakeout:timecreated',
                'timemodified' => 'privacy:metadata:paygw_shakeout:timemodified',
            ],
            'privacy:metadata:paygw_shakeout'
        );

        $items->add_external_location_link(
            'shakeout',
            [
                'first_name' => 'privacy:metadata:shakeout:first_name',
                'last_name' => 'privacy:metadata:shakeout:last_name',
                'email' => 'privacy:metadata:shakeout:email',
                'phone' => 'privacy:metadata:shakeout:phone',
                'address' => 'privacy:metadata:shakeout:address',
                'amount' => 'privacy:metadata:shakeout:amount',
                'currency' => 'privacy:metadata:shakeout:currency',
            ],
            'privacy:metadata:shakeout'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        
        $sql = "SELECT DISTINCT ctx.id
                  FROM {paygw_shakeout} ps
                  JOIN {payments} p ON p.id = ps.paymentid
                  JOIN {context} ctx ON ctx.instanceid = p.userid AND ctx.contextlevel = :contextlevel
                 WHERE p.userid = :userid";

        $params = [
            'userid' => $userid,
            'contextlevel' => CONTEXT_USER,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            $sql = "SELECT p.userid
                      FROM {paygw_shakeout} ps
                      JOIN {payments} p ON p.id = ps.paymentid
                     WHERE p.userid = :userid";

            $params = ['userid' => $context->instanceid];
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }

    /**
     * Export personal data for the given approved_contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_user && $context->instanceid == $user->id) {
                $sql = "SELECT ps.*, p.component, p.paymentarea, p.itemid, p.amount, p.currency
                          FROM {paygw_shakeout} ps
                          JOIN {payments} p ON p.id = ps.paymentid
                         WHERE p.userid = :userid";

                $params = ['userid' => $user->id];
                $records = $DB->get_records_sql($sql, $params);

                if (!empty($records)) {
                    $data = [];
                    foreach ($records as $record) {
                        $data[] = [
                            'invoice_id' => $record->invoice_id,
                            'invoice_ref' => $record->invoice_ref,
                            'status' => $record->status,
                            'amount' => $record->amount,
                            'currency' => $record->currency,
                            'component' => $record->component,
                            'paymentarea' => $record->paymentarea,
                            'timecreated' => transform::datetime($record->timecreated),
                            'timemodified' => $record->timemodified ? transform::datetime($record->timemodified) : null,
                        ];
                    }

                    writer::with_context($context)->export_data(
                        [get_string('pluginname', 'paygw_shakeout')],
                        (object) $data
                    );
                }
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context instanceof \context_user) {
            $sql = "DELETE FROM {paygw_shakeout} 
                     WHERE paymentid IN (
                        SELECT id FROM {payments} WHERE userid = :userid
                     )";
            $DB->execute($sql, ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_user && $context->instanceid == $user->id) {
                $sql = "DELETE FROM {paygw_shakeout} 
                         WHERE paymentid IN (
                            SELECT id FROM {payments} WHERE userid = :userid
                         )";
                $DB->execute($sql, ['userid' => $user->id]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();

        if ($context instanceof \context_user && in_array($context->instanceid, $userids)) {
            $sql = "DELETE FROM {paygw_shakeout} 
                     WHERE paymentid IN (
                        SELECT id FROM {payments} WHERE userid = :userid
                     )";
            $DB->execute($sql, ['userid' => $context->instanceid]);
        }
    }
}
