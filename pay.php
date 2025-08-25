
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
 * Shake-Out payment processing
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/course/lib.php');

use core_payment\helper;
use paygw_shakeout\shakeout_helper;

require_login();

$component = required_param('component', PARAM_ALPHANUMEXT);
$paymentarea = required_param('paymentarea', PARAM_ALPHANUMEXT);  
$itemid = required_param('itemid', PARAM_INT);
$description = required_param('description', PARAM_TEXT);

$config = (object) helper::get_gateway_configuration($component, $paymentarea, $itemid, 'shakeout');
$payable = helper::get_payable($component, $paymentarea, $itemid);
$surcharge = helper::get_gateway_surcharge('shakeout');
$cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);

// Prepare invoice data for Shake-Out API
$datetime = new DateTime('tomorrow');
$invoicedata = [
    'amount' => $cost,
    'currency' => $payable->get_currency(),
    'due_date' => $datetime->format('Y-m-d'),
    'customer' => [
        'first_name' => $USER->firstname,
        'last_name' => $USER->lastname,
        'email' => $USER->email,
        'phone' => isset($USER->phone1) ? $USER->phone1 : '',
        'address' => isset($USER->address) ? $USER->address : ''
    ],
    'redirection_urls' => [
        'success_url' => $config->successurl ?: $CFG->wwwroot,
        'fail_url' => $config->failureurl ?: $CFG->wwwroot,
        'pending_url' => $config->pendingurl ?: $CFG->wwwroot
    ],
    'invoice_items' => [[
        'name' => $description,
        'price' => $cost,
        'quantity' => 1
    ]]
];

try {
    $response = shakeout_helper::create_invoice($config->apikey, $invoicedata);
    
    if ($response['status'] === 'success') {
        // Store payment record
        $paymentrecord = helper::save_payment(
            helper::get_payable($component, $paymentarea, $itemid)->get_account_id(),
            $component,
            $paymentarea,
            $itemid,
            $USER->id,
            $cost,
            $payable->get_currency(),
            'shakeout'
        );

        // Store Shake-Out specific data
        $DB->insert_record('paygw_shakeout', [
            'paymentid' => $paymentrecord->get_id(),
            'invoice_id' => $response['data']['invoice_id'],
            'invoice_ref' => $response['data']['invoice_ref'],
            'invoice_url' => $response['data']['url'],
            'timecreated' => time()
        ]);

        // Redirect to Shake-Out payment page
        redirect($response['data']['url']);
    } else {
        $errors = '';
        if (isset($response['errors'])) {
            foreach ($response['errors'] as $err) {
                $errors .= is_array($err) ? $err[0] . "\n" : $err . "\n";
            }
        } else {
            $errors = $response['message'] ?? get_string('unknownerror', 'paygw_shakeout');
        }
        throw new moodle_exception('paymentfailed', 'paygw_shakeout', '', null, $errors);
    }
} catch (Exception $e) {
    throw new moodle_exception('paymentfailed', 'paygw_shakeout', '', null, $e->getMessage());
}
