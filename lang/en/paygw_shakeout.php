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
 * Plugin strings are defined here.
 *
 * @package     paygw_shakeout
 * @category    string
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Shake-Out Payment Gateway';
$string['pluginname_desc'] = 'The Shake-Out plugin allows you to receive payments via Shake-Out payment gateway.';
$string['gatewayname'] = 'Shake-Out';
$string['gatewaydescription'] = 'Accept payments via FawryPay, Meeza, E-wallets, and Credit/Debit Cards through Shake-Out';

// Configuration
$string['apikey'] = 'API Key';
$string['apikey_help'] = 'Get this from your Shake-Out dashboard at https://dash.shake-out.com/integrations';
$string['secretkey'] = 'Secret Key'; 
$string['secretkey_help'] = 'Get this from your Shake-Out dashboard at https://dash.shake-out.com/integrations';
$string['successurl'] = 'Success URL';
$string['successurl_help'] = 'URL to redirect users after successful payment';
$string['failureurl'] = 'Failure URL';
$string['failureurl_help'] = 'URL to redirect users after failed payment';
$string['pendingurl'] = 'Pending URL';
$string['pendingurl_help'] = 'URL to redirect users when payment is pending';

// Errors
$string['gatewaycannotbeenabled'] = 'The payment gateway cannot be enabled because the configuration is incomplete.';
$string['internalerror'] = 'An internal error occurred';
$string['unknownerror'] = 'Unknown error occurred';
$string['paymentfailed'] = 'Payment failed: {$a}';
$string['invalidjsonresponse'] = 'Invalid JSON response from Shake-Out API';
$string['apierror'] = 'API Error: {$a}';

// Payment
$string['paymentmethod'] = 'Shake-Out Payment';
$string['paymentmethoddescription'] = 'Pay with FawryPay, Meeza, E-wallets, or Credit/Debit Cards';
$string['redirecting'] = 'Redirecting to Shake-Out payment page...';
$string['proceedtopayment'] = 'Proceed to Payment';
