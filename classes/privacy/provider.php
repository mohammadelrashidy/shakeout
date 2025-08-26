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
 * Privacy Subsystem implementation for paygw_shakeout.
 *
 * @package    paygw_shakeout
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('paygw_shakeout', [
            'paymentid' => 'privacy:metadata:paygw_shakeout:paymentid',
            'invoice_id' => 'privacy:metadata:paygw_shakeout:invoice_id',
            'invoice_ref' => 'privacy:metadata:paygw_shakeout:invoice_ref',
            'invoice_url' => 'privacy:metadata:paygw_shakeout:invoice_url',
            'status' => 'privacy:metadata:paygw_shakeout:status',
            'timecreated' => 'privacy:metadata:paygw_shakeout:timecreated',
            'timemodified' => 'privacy:metadata:paygw_shakeout:timemodified',
        ], 'privacy:metadata:paygw_shakeout');

        $collection->add_external_location_link('shakeout', [
            'first_name' => 'privacy:metadata:shakeout:first_name',
            'last_name' => 'privacy:metadata:shakeout:last_name',
            'email' => 'privacy:metadata:shakeout:email',
            'phone' => 'privacy:metadata:shakeout:phone',
            'address' => 'privacy:metadata:shakeout:address',
            'amount' => 'privacy:metadata:shakeout:amount',
            'currency' => 'privacy:metadata:shakeout:currency',
        ], 'privacy:metadata:shakeout');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT DISTINCT ctx.id
                  FROM {paygw_shakeout} ps
                  JOIN {payments} p ON ps.paymentid = p.id
                  JOIN {context} ctx ON p.accountid = ctx.id
                 WHERE p.userid = :userid";

        $contextlist->add_from_sql($sql, ['userid' => $userid]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        $sql = "SELECT p.userid
                  FROM {paygw_shakeout} ps
                  JOIN {payments} p ON ps.paymentid = p.id
                 WHERE p.accountid = :accountid";

        $userlist->add_from_sql('userid', $sql, ['accountid' => $context->id]);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT ps.*, p.component, p.paymentarea, p.itemid, p.amount, p.currency
                  FROM {paygw_shakeout} ps
                  JOIN {payments} p ON ps.paymentid = p.id
                 WHERE p.userid = :userid
                   AND p.accountid {$contextsql}";

        $params = ['userid' => $userid] + $contextparams;
        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $record) {
            $context = \context::instance_by_id($record->accountid);
            $data = [
                'invoice_id' => $record->invoice_id,
                'invoice_ref' => $record->invoice_ref,
                'status' => $record->status,
                'amount' => $record->amount,
                'currency' => $record->currency,
                'timecreated' => transform::datetime($record->timecreated),
                'timemodified' => $record->timemodified ? transform::datetime($record->timemodified) : null,
            ];

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'paygw_shakeout')], 
                (object) $data
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $sql = "DELETE ps FROM {paygw_shakeout} ps
                  JOIN {payments} p ON ps.paymentid = p.id
                 WHERE p.accountid = :accountid";

        $DB->execute($sql, ['accountid' => $context->id]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "DELETE ps FROM {paygw_shakeout} ps
                  JOIN {payments} p ON ps.paymentid = p.id
                 WHERE p.userid = :userid
                   AND p.accountid {$contextsql}";

        $params = ['userid' => $userid] + $contextparams;
        $DB->execute($sql, $params);
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

        if (empty($userids)) {
            return;
        }

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $sql = "DELETE ps FROM {paygw_shakeout} ps
                  JOIN {payments} p ON ps.paymentid = p.id
                 WHERE p.accountid = :accountid
                   AND p.userid {$usersql}";

        $params = ['accountid' => $context->id] + $userparams;
        $DB->execute($sql, $params);
    }
}
