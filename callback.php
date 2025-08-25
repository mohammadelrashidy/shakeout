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
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        shakeout_helper::log_payment_activity('error', 'Payment not found for invoice', ['invoice_id' => $data['data']['invoice_id']]);
        http_response_code(404);
        echo 'Payment not found';
        exit;
    }

    $paymentrecord = $DB->get_record('payments', ['id' => $shakeoutrecord->paymentid]);
    
    if (!$paymentrecord) {
        shakeout_helper::log_payment_activity('error', 'Payment record not found', ['payment_id' => $shakeoutrecord->paymentid]);
        http_response_code(404);
        echo 'Payment record not found';
        exit;
    }

    // Get gateway config for signature verification.
    $config = (object) \core_payment\helper::get_gateway_configuration(
        $paymentrecord->component,
        $paymentrecord->paymentarea,
        $paymentrecord->itemid,
        'shakeout'
    );

    // Verify signature if secret key is configured.
    if (!empty($config->secretkey) && isset($data['signature'])) {
        if (!shakeout_helper::verify_signature($data, $data['signature'], $config->secretkey)) {
            shakeout_helper::log_payment_activity('error', 'Invalid signature in webhook', [
                'received_signature' => $data['signature'],
                'invoice_id' => $data['data']['invoice_id']
            ]);
            http_response_code(400);
            echo 'Invalid signature';
            exit;
        }
        shakeout_helper::log_payment_activity('info', 'Webhook signature verified successfully');
    }

    // Process payment status.
    $invoicestatus = $data['data']['invoice_status'] ?? '';
    $currentstatus = $shakeoutrecord->status ?? 'pending';

    shakeout_helper::log_payment_activity('info', 'Processing payment status update', [
        'invoice_id' => $data['data']['invoice_id'],
        'new_status' => $invoicestatus,
        'current_status' => $currentstatus
    ]);

    // Update payment status based on invoice status.
    $updated = false;
    
    switch ($invoicestatus) {
        case 'paid':
            if ($currentstatus !== 'paid') {
                // Update payment status.
                $paymentrecord->success = 1;
                $DB->update_record('payments', $paymentrecord);

                // Update Shake-Out record.
                $shakeoutrecord->status = 'paid';
                $shakeoutrecord->timemodified = time();
                $DB->update_record('paygw_shakeout', $shakeoutrecord);

                // Deliver what was paid for.
                \core_payment\helper::deliver_order(
                    $paymentrecord->component,
                    $paymentrecord->paymentarea,
                    $paymentrecord->itemid,
                    $paymentrecord->id,
                    $paymentrecord->userid
                );

                $updated = true;
                shakeout_helper::log_payment_activity('info', 'Payment completed successfully', [
                    'payment_id' => $paymentrecord->id,
                    'invoice_id' => $data['data']['invoice_id']
                ]);
            }
            break;

        case 'cancelled':
        case 'failed':
        case 'expired':
            if ($currentstatus !== $invoicestatus) {
                // Update Shake-Out record.
                $shakeoutrecord->status = $invoicestatus;
                $shakeoutrecord->timemodified = time();
                $DB->update_record('paygw_shakeout', $shakeoutrecord);

                $updated = true;
                shakeout_helper::log_payment_activity('info', 'Payment status updated', [
                    'payment_id' => $paymentrecord->id,
                    'invoice_id' => $data['data']['invoice_id'],
                    'status' => $invoicestatus
                ]);
            }
            break;

        case 'pending':
        case 'processing':
            if ($currentstatus !== $invoicestatus) {
                // Update Shake-Out record.
                $shakeoutrecord->status = $invoicestatus;
                $shakeoutrecord->timemodified = time();
                $DB->update_record('paygw_shakeout', $shakeoutrecord);

                $updated = true;
                shakeout_helper::log_payment_activity('info', 'Payment status updated', [
                    'payment_id' => $paymentrecord->id,
                    'invoice_id' => $data['data']['invoice_id'],
                    'status' => $invoicestatus
                ]);
            }
            break;

        default:
            shakeout_helper::log_payment_activity('warning', 'Unknown payment status received', [
                'status' => $invoicestatus,
                'invoice_id' => $data['data']['invoice_id']
            ]);
            break;
    }

    // Send success response.
    http_response_code(200);
    echo 'OK';
    
    if ($updated) {
        shakeout_helper::log_payment_activity('info', 'Webhook processed successfully');
    } else {
        shakeout_helper::log_payment_activity('info', 'Webhook received but no updates needed');
    }

} catch (Exception $e) {
    shakeout_helper::log_payment_activity('error', 'Webhook processing exception', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'webhook_data' => $data ?? null
    ]);
    
    http_response_code(500);
    echo 'Server error';
    error_log('Shake-Out webhook error: ' . $e->getMessage());
}
