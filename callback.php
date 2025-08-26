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
 * Shake-Out webhook callback
 *
 * @package    paygw_shakeout
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use core_payment\helper;
use paygw_shakeout\shakeout_helper;

// Disable Moodle's debug messages and any output.
define('NO_DEBUG_DISPLAY', true);

// Get raw POST data.
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    shakeout_helper::log_payment_activity('error', 'Invalid JSON received in webhook', ['raw_data' => $json]);
    http_response_code(400);
    echo 'Invalid JSON';
    exit;
}

try {
    global $DB;

    // Log webhook received.
    shakeout_helper::log_payment_activity('info', 'Webhook received', ['data' => $data]);

    // Validate required fields.
    if (!isset($data['data']['invoice_id'])) {
        shakeout_helper::log_payment_activity('error', 'Missing invoice_id in webhook data');
        http_response_code(400);
        echo 'Missing invoice_id';
        exit;
    }

    // Get payment record by invoice ID.
    $shakeoutrecord = $DB->get_record('paygw_shakeout', ['invoice_id' => $data['data']['invoice_id']]);
    if (!$shakeoutrecord) {
        shakeout_helper::log_payment_activity('error', 'Payment not found for invoice', 
                                            ['invoice_id' => $data['data']['invoice_id']]);
        http_response_code(404);
        echo 'Payment not found';
        exit;
    }

    $paymentrecord = $DB->get_record('payments', ['id' => $shakeoutrecord->paymentid]);
    if (!$paymentrecord) {
        shakeout_helper::log_payment_activity('error', 'Payment record not found', 
                                            ['payment_id' => $shakeoutrecord->paymentid]);
        http_response_code(404);
        echo 'Payment record not found';
        exit;
    }

    // Get gateway configuration for signature verification.
    $config = (object) helper::get_gateway_configuration(
        $paymentrecord->component,
        $paymentrecord->paymentarea,
        $paymentrecord->itemid,
        'shakeout'
    );

    // Verify webhook signature if secret key is available.
    if (!empty($config->secretkey) && isset($data['signature'])) {
        $isvalidsignature = shakeout_helper::verify_signature($data, $data['signature'], $config->secretkey);
        if (!$isvalidsignature) {
            shakeout_helper::log_payment_activity('error', 'Invalid webhook signature', [
                'invoice_id' => $data['data']['invoice_id'],
                'received_signature' => $data['signature']
            ]);
            http_response_code(401);
            echo 'Invalid signature';
            exit;
        }
    }

    // Process the webhook based on payment status.
    $newstatus = strtolower($data['data']['status'] ?? 'unknown');
    $oldstatus = $shakeoutrecord->status;

    // Update Shake-Out record.
    $updatedata = [
        'id' => $shakeoutrecord->id,
        'status' => $newstatus,
        'timemodified' => time()
    ];

    $DB->update_record('paygw_shakeout', $updatedata);

    // Log status change.
    shakeout_helper::log_payment_activity('info', 'Payment status updated', [
        'invoice_id' => $data['data']['invoice_id'],
        'old_status' => $oldstatus,
        'new_status' => $newstatus,
        'payment_id' => $paymentrecord->id
    ]);

    // Handle different payment statuses.
    switch ($newstatus) {
        case 'paid':
        case 'completed':
        case 'success':
            // Payment successful - deliver the product/service.
            try {
                helper::deliver_order(
                    $paymentrecord->component,
                    $paymentrecord->paymentarea,
                    $paymentrecord->itemid,
                    $paymentrecord->id,
                    $paymentrecord->userid
                );

                shakeout_helper::log_payment_activity('info', 'Payment delivered successfully', [
                    'payment_id' => $paymentrecord->id,
                    'user_id' => $paymentrecord->userid
                ]);

                // Send success notification to user if email is available
                if (!empty($USER->email)) {
                    $subject = get_string('paymentsuccess_subject', 'paygw_shakeout');
                    $message = get_string('paymentsuccess_message', 'paygw_shakeout', [
                        'amount' => shakeout_helper::format_amount($paymentrecord->amount, $paymentrecord->currency),
                        'invoice_id' => $data['data']['invoice_id']
                    ]);
                    
                    // Use Moodle's email system
                    $user = $DB->get_record('user', ['id' => $paymentrecord->userid]);
                    if ($user) {
                        email_to_user($user, get_admin(), $subject, $message);
                    }
                }

            } catch (Exception $e) {
                shakeout_helper::log_payment_activity('error', 'Failed to deliver payment', [
                    'payment_id' => $paymentrecord->id,
                    'exception' => $e->getMessage()
                ]);
            }
            break;

        case 'failed':
        case 'cancelled':
        case 'expired':
            // Payment failed - log for administrator review.
            shakeout_helper::log_payment_activity('warning', 'Payment failed or cancelled', [
                'payment_id' => $paymentrecord->id,
                'status' => $newstatus,
                'user_id' => $paymentrecord->userid
            ]);
            break;

        case 'pending':
        case 'processing':
            // Payment is still being processed - no action needed.
            shakeout_helper::log_payment_activity('info', 'Payment still processing', [
                'payment_id' => $paymentrecord->id,
                'status' => $newstatus
            ]);
            break;

        default:
            // Unknown status - log for investigation.
            shakeout_helper::log_payment_activity('warning', 'Unknown payment status received', [
                'payment_id' => $paymentrecord->id,
                'status' => $newstatus,
                'data' => $data
            ]);
            break;
    }

    // Respond with success.
    http_response_code(200);
    echo 'OK';

} catch (Exception $e) {
    // Log any exceptions.
    shakeout_helper::log_payment_activity('error', 'Webhook processing failed', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'data' => $data ?? null
    ]);

    http_response_code(500);
    echo 'Internal server error';
}
