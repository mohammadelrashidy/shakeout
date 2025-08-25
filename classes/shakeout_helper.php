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
 * Helper class for Shake-Out API integration.
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class shakeout_helper {

    /** @var string API base URL for production */
    const API_BASE_URL = 'https://api.shake-out.com/api/v1/';

    /** @var string API base URL for sandbox */
    const API_SANDBOX_URL = 'https://sandbox-api.shake-out.com/api/v1/';

    /**
     * Create an invoice using Shake-Out API
     *
     * @param string $apikey API key for authentication
     * @param array $invoicedata Invoice data
     * @param bool $sandbox Whether to use sandbox environment
     * @return array API response
     * @throws \moodle_exception
     */
    public static function create_invoice(string $apikey, array $invoicedata, bool $sandbox = false): array {
        global $CFG;

        $baseurl = $sandbox ? self::API_SANDBOX_URL : self::API_BASE_URL;
        $url = $baseurl . 'invoices';

        // Add webhook URL to invoice data
        $invoicedata['webhook_url'] = $CFG->wwwroot . '/payment/gateway/shakeout/callback.php';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($invoicedata),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apikey,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => !$sandbox, // Disable SSL verification in sandbox
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \moodle_exception('curlerror', 'paygw_shakeout', '', null, $error);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception('invalidjson', 'paygw_shakeout', '', null, $response);
        }

        if ($httpcode >= 400) {
            $errormsg = isset($decoded['message']) ? $decoded['message'] : 'HTTP Error ' . $httpcode;
            throw new \moodle_exception('apierror', 'paygw_shakeout', '', null, $errormsg);
        }

        return $decoded;
    }

    /**
     * Verify webhook signature from Shake-Out
     *
     * @param array $data Webhook payload data
     * @param string $signature Received signature
     * @param string $secretkey Secret key for verification
     * @return bool True if signature is valid
     */
    public static function verify_signature(array $data, string $signature, string $secretkey): bool {
        // Remove signature from data before verification
        unset($data['signature']);
        
        // Sort data by keys for consistent signature generation
        ksort($data);
        
        // Create signature string
        $signaturestring = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $signaturestring .= $key . '=' . json_encode($value) . '&';
            } else {
                $signaturestring .= $key . '=' . $value . '&';
            }
        }
        $signaturestring = rtrim($signaturestring, '&');
        
        // Generate expected signature
        $expectedsignature = hash_hmac('sha256', $signaturestring, $secretkey);
        
        return hash_equals($expectedsignature, $signature);
    }

    /**
     * Get invoice status from Shake-Out API
     *
     * @param string $apikey API key for authentication
     * @param string $invoiceid Invoice ID
     * @param bool $sandbox Whether to use sandbox environment
     * @return array API response
     * @throws \moodle_exception
     */
    public static function get_invoice_status(string $apikey, string $invoiceid, bool $sandbox = false): array {
        $baseurl = $sandbox ? self::API_SANDBOX_URL : self::API_BASE_URL;
        $url = $baseurl . 'invoices/' . $invoiceid;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apikey,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => !$sandbox,
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \moodle_exception('curlerror', 'paygw_shakeout', '', null, $error);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception('invalidjson', 'paygw_shakeout', '', null, $response);
        }

        if ($httpcode >= 400) {
            $errormsg = isset($decoded['message']) ? $decoded['message'] : 'HTTP Error ' . $httpcode;
            throw new \moodle_exception('apierror', 'paygw_shakeout', '', null, $errormsg);
        }

        return $decoded;
    }

    /**
     * Format amount for display
     *
     * @param float $amount Amount
     * @param string $currency Currency code
     * @return string Formatted amount
     */
    public static function format_amount(float $amount, string $currency): string {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Log payment activity
     *
     * @param string $level Log level (info, warning, error)
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public static function log_payment_activity(string $level, string $message, array $context = []): void {
        global $CFG;
        
        if (!empty($CFG->debugdeveloper)) {
            $logmessage = "Shake-Out Payment: [{$level}] {$message}";
            if (!empty($context)) {
                $logmessage .= " Context: " . json_encode($context);
            }
            error_log($logmessage);
        }
    }
}
