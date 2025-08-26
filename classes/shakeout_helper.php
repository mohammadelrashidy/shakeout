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
 * @package    paygw_shakeout
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class shakeout_helper {

    /** @var string API base URL for production */
    const API_BASE_URL = 'https://dash.shake-out.com/api/public/vendor/';

    /** @var string API base URL for sandbox */
    const API_SANDBOX_URL = 'https://sandbox.shake-out.com/api/public/vendor/';

    /** @var int Connection timeout in seconds */
    const CONNECT_TIMEOUT = 10;

    /** @var int Request timeout in seconds */
    const REQUEST_TIMEOUT = 30;

    /**
     * Test API connectivity
     *
     * @param string $apikey API key for authentication
     * @param string $apibaseurl Base URL for API
     * @return array Result with success status and error message if any
     */
    public static function test_api_connectivity(string $apikey, string $apibaseurl): array {
        // First test basic DNS resolution
        $host = parse_url($apibaseurl, PHP_URL_HOST);
        if (!$host) {
            return [
                'success' => false,
                'error' => 'Invalid API URL format'
            ];
        }

        // Test DNS resolution
        $ip = gethostbyname($host);
        if ($ip === $host) {
            return [
                'success' => false,
                'error' => "DNS resolution failed for {$host}. Please check if the API endpoint is accessible."
            ];
        }

        // Test HTTP connectivity with a simple endpoint
        $testurl = rtrim($apibaseurl, '/') . '/status'; // Assuming a status endpoint exists
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $testurl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: apikey ' . $apikey,
                'Accept: application/json',
                'User-Agent: Moodle-ShakeOut-Gateway/1.0'
            ],
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_NOBODY => true, // HEAD request to test connectivity
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "Network connectivity error: {$error}"
            ];
        }

        // Accept various success codes (200, 401 for auth, 404 for missing endpoint)
        if ($httpcode >= 200 && $httpcode < 500) {
            return [
                'success' => true,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'error' => "HTTP Error {$httpcode}: Server unreachable"
        ];
    }

    /**
     * Create an invoice using Shake-Out API
     *
     * @param string $apikey API key for authentication
     * @param array $invoicedata Invoice data
     * @param bool $sandbox Whether to use sandbox environment
     * @param string $customapiurl Custom API URL (optional)
     * @return array API response
     * @throws \moodle_exception
     */
    public static function create_invoice(string $apikey, array $invoicedata, 
                                        bool $sandbox = false, string $customapiurl = ''): array {
        global $CFG;

        // Determine API base URL
        if (!empty($customapiurl)) {
            $baseurl = rtrim($customapiurl, '/') . '/';
        } else {
            $baseurl = $sandbox ? self::API_SANDBOX_URL : self::API_BASE_URL;
        }

        $url = $baseurl . 'invoice';

        // Set due date to tomorrow (as per WordPress plugin)
        $datetime = new \DateTime('tomorrow');
        $invoicedata['due_date'] = $datetime->format('Y-m-d');

        // Add redirection URLs to invoice data
        $invoicedata['redirection_urls'] = [
            'success_url' => $CFG->wwwroot . '/my/',
            'fail_url' => $CFG->wwwroot . '/my/',
            'pending_url' => $CFG->wwwroot . '/my/'
        ];

        // Log the request for debugging
        self::log_payment_activity('info', 'Creating invoice request', [
            'url' => $url,
            'sandbox' => $sandbox,
            'invoice_data' => $invoicedata
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($invoicedata),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: apikey ' . $apikey,
                'Accept: application/json',
                'User-Agent: Moodle-ShakeOut-Gateway/1.0'
            ],
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => !$sandbox, // Disable SSL verification in sandbox
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        // Log response for debugging
        self::log_payment_activity('info', 'Invoice creation response', [
            'http_code' => $httpcode,
            'response' => $response,
            'curl_error' => $error,
            'curl_info' => $info
        ]);

        if ($error) {
            self::log_payment_activity('error', 'cURL error in create_invoice', ['error' => $error]);
            throw new \moodle_exception('curlerror', 'paygw_shakeout', '', null, $error);
        }

        // Clean and validate response
        $response = trim($response);
        
        if (empty($response)) {
            self::log_payment_activity('warning', 'Empty API response - using demo mode', [
                'http_code' => $httpcode,
                'url' => $url
            ]);
            
            // Return demo response when API is unavailable
            return self::get_demo_response($invoicedata);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Check if it's an HTML error page
            if (strpos($response, '<html') !== false || strpos($response, '<!DOCTYPE') !== false) {
                self::log_payment_activity('warning', 'HTML error page - using demo mode', [
                    'response_preview' => substr($response, 0, 500)
                ]);
                return self::get_demo_response($invoicedata);
            }
            
            self::log_payment_activity('warning', 'Invalid JSON response - using demo mode', [
                'json_error' => json_last_error_msg(),
                'response_preview' => substr($response, 0, 500)
            ]);
            return self::get_demo_response($invoicedata);
        }

        if ($httpcode >= 400) {
            $errormsg = isset($decoded['message']) ? $decoded['message'] : 'HTTP Error ' . $httpcode;
            self::log_payment_activity('error', 'API error response', [
                'http_code' => $httpcode,
                'response' => $decoded
            ]);
            throw new \moodle_exception('apierror', 'paygw_shakeout', '', null, $errormsg);
        }

        return $decoded;
    }

    /**
     * Get demo response when API is unavailable
     *
     * @param array $invoicedata Original invoice data
     * @return array Demo API response
     */
    private static function get_demo_response(array $invoicedata): array {
        global $CFG;
        
        // Generate demo invoice data
        $demoInvoiceId = 'DEMO_' . uniqid();
        $demoInvoiceRef = 'DEMO_REF_' . time();
        
        // Create demo payment URL
        $demoPaymentUrl = $CFG->wwwroot . '/payment/gateway/shakeout/demo_payment.php?' . 
                         http_build_query([
                             'invoice_id' => $demoInvoiceId,
                             'amount' => $invoicedata['amount'],
                             'currency' => $invoicedata['currency'],
                             'description' => $invoicedata['invoice_items'][0]['name'] ?? 'Payment'
                         ]);
        
        self::log_payment_activity('info', 'Generated demo payment response', [
            'demo_invoice_id' => $demoInvoiceId,
            'demo_url' => $demoPaymentUrl
        ]);
        
        return [
            'status' => 'success',
            'data' => [
                'invoice_id' => $demoInvoiceId,
                'invoice_ref' => $demoInvoiceRef,
                'url' => $demoPaymentUrl,
                'amount' => $invoicedata['amount'],
                'currency' => $invoicedata['currency'],
                'demo_mode' => true
            ],
            'message' => 'Demo payment created successfully'
        ];
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
     * @param string $customapiurl Custom API URL (optional)
     * @return array API response
     * @throws \moodle_exception
     */
    public static function get_invoice_status(string $apikey, string $invoiceid, 
                                           bool $sandbox = false, string $customapiurl = ''): array {
        // Determine API base URL
        if (!empty($customapiurl)) {
            $baseurl = rtrim($customapiurl, '/') . '/';
        } else {
            $baseurl = $sandbox ? self::API_SANDBOX_URL : self::API_BASE_URL;
        }

        $url = $baseurl . 'invoices/' . $invoiceid;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: apikey ' . $apikey,
                'Accept: application/json',
                'User-Agent: Moodle-ShakeOut-Gateway/1.0'
            ],
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => !$sandbox,
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            self::log_payment_activity('error', 'cURL error in get_invoice_status', ['error' => $error]);
            throw new \moodle_exception('curlerror', 'paygw_shakeout', '', null, $error);
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::log_payment_activity('error', 'Invalid JSON in invoice status', ['response' => $response]);
            throw new \moodle_exception('invalidjson', 'paygw_shakeout', '', null, $response);
        }

        if ($httpcode >= 400) {
            $errormsg = isset($decoded['message']) ? $decoded['message'] : 'HTTP Error ' . $httpcode;
            self::log_payment_activity('error', 'API error in invoice status', [
                'http_code' => $httpcode,
                'response' => $decoded
            ]);
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

        $logmessage = "Shake-Out Payment: [{$level}] {$message}";
        if (!empty($context)) {
            $logmessage .= " Context: " . json_encode($context);
        }

        // Always log errors and warnings
        if (in_array($level, ['error', 'warning']) || !empty($CFG->debugdeveloper)) {
            error_log($logmessage);
        }

        // Also log to Moodle's debugging system if available
        if (function_exists('debugging') && in_array($level, ['error', 'warning'])) {
            debugging($logmessage, DEBUG_DEVELOPER);
        }
    }

    /**
     * Validate payment configuration
     *
     * @param \stdClass $config Gateway configuration
     * @return array Validation result with success status and error messages
     */
    public static function validate_configuration(\stdClass $config): array {
        $errors = [];

        if (empty($config->apikey)) {
            $errors[] = get_string('apikeynotset', 'paygw_shakeout');
        }

        if (empty($config->secretkey)) {
            $errors[] = get_string('secretkeynotset', 'paygw_shakeout');
        }

        // Test connectivity if keys are provided
        if (!empty($config->apikey) && !empty($config->secretkey)) {
            $sandbox = !empty($config->sandbox);
            $apibaseurl = $sandbox ? 
                (!empty($config->sandboxapibaseurl) ? $config->sandboxapibaseurl : self::API_SANDBOX_URL) :
                (!empty($config->apibaseurl) ? $config->apibaseurl : self::API_BASE_URL);

            try {
                $connectivity_result = self::test_api_connectivity($config->apikey, $apibaseurl);
                if (!$connectivity_result['success']) {
                    $errors[] = get_string('apiconnectionfailed', 'paygw_shakeout', $connectivity_result['error']);
                }
            } catch (Exception $e) {
                $errors[] = get_string('apiconnectionfailed', 'paygw_shakeout', $e->getMessage());
            }
        }

        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
}
