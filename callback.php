
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

// Get raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo 'Invalid JSON';
    exit;
}

try {
    global $DB;
    
    // Get payment record by invoice ID
    $shakeoutrecord = $DB->get_record('paygw_shakeout', ['invoice_id' => $data['data']['invoice_id']]);
    
    if (!$shakeoutrecord) {
        http_response_code(404);
        echo 'Payment not found';
        exit;
    }
    
    $paymentrecord = $DB->get_record('payments', ['id' => $shakeoutrecord->paymentid]);
    
    if (!$paymentrecord) {
        http_response_code(404);
        echo 'Payment record not found';
        exit;
    }
    
    // Get gateway config for signature verification
    $config = (object) helper::get_gateway_configuration(
        $paymentrecord->component, 
        $paymentrecord->paymentarea, 
        $paymentrecord->itemid, 
        'shakeout'
    );
    
    // Verify signature
    if (!shakeout_helper::verify_signature($data, $data['signature'], $config->secretkey)) {
        http_response_code(400);
        echo 'Invalid signature';
        exit;
    }
    
    // Process payment status
    if ($data['data']['invoice_status'] === 'paid') {
        // Update payment status
        $paymentrecord->success = 1;
        $DB->update_record('payments', $paymentrecord);
        
        // Update Shake-Out record
        $shakeoutrecord->status = 'paid';
        $shakeoutrecord->timemodified = time();
        $DB->update_record('paygw_shakeout', $shakeoutrecord);
        
        // Deliver what was paid for
        helper::deliver_order(
            $paymentrecord->component,
            $paymentrecord->paymentarea,
            $paymentrecord->itemid,
            $paymentrecord->id,
            $paymentrecord->userid
        );
    }
    
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Server error';
    error_log('Shake-Out webhook error: ' . $e->getMessage());
}
