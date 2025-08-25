
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
 * Shake-Out API helper
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_shakeout;

use context_system;
use core_payment\helper;
use stdClass;

class shakeout_helper {
    
    /**
     * @var string Shake-Out API endpoint
     */
    const API_ENDPOINT = 'https://dash.shake-out.com/api/public/vendor/invoice';

    /**
     * Create an invoice with Shake-Out
     *
     * @param string $apikey
     * @param array $invoicedata
     * @return array
     */
    public static function create_invoice($apikey, $invoicedata) {
        $curl = new \curl();
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: apikey ' . $apikey
        ];
        
        $curl->setopt([
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($invoicedata),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = $curl->post(self::API_ENDPOINT);
        $httpcode = $curl->info['http_code'];
        
        if ($httpcode !== 200) {
            throw new \moodle_exception('apierror', 'paygw_shakeout', '', null, 
                'HTTP Error: ' . $httpcode . ' - ' . $response);
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception('invalidjsonresponse', 'paygw_shakeout');
        }

        return $data;
    }

    /**
     * Verify webhook signature
     *
     * @param array $data Webhook data
     * @param string $signature Received signature
     * @param string $secretkey Secret key
     * @return bool
     */
    public static function verify_signature($data, $signature, $secretkey) {
        $calculatedSignature = hash('sha256', 
            $data['data']['invoice_id'] . 
            $data['data']['amount'] . 
            $data['data']['invoice_status'] . 
            $data['data']['updated_at'] . 
            $secretkey
        );
        
        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Get payment details for order processing
     *
     * @param string $component
     * @param string $paymentarea
     * @param int $itemid
     * @param string $userid
     * @return stdClass
     */
    public static function get_payment_details($component, $paymentarea, $itemid, $userid) {
        global $DB, $USER;
        
        $payable = helper::get_payable($component, $paymentarea, $itemid);
        $surcharge = helper::get_gateway_surcharge('shakeout');
        $cost = helper::get_rounded_cost($payable->get_amount(), $payable->get_currency(), $surcharge);

        $user = $DB->get_record('user', ['id' => $userid]);
        
        $details = new stdClass();
        $details->amount = $cost;
        $details->currency = $payable->get_currency();
        $details->description = $payable->get_description();
        $details->user = $user;
        $details->component = $component;
        $details->paymentarea = $paymentarea;
        $details->itemid = $itemid;
        
        return $details;
    }
}
