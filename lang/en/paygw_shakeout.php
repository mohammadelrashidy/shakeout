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

$string['pluginname'] = 'Shake-Out';
$string['pluginname_desc'] = 'The Shake-Out plugin allows you to receive payments via Shake-Out payment gateway.';
$string['gatewayname'] = 'Shake-Out';
$string['gatewaydescription'] = 'Shake-Out is an authorised payment gateway provider for processing credit card transactions.';

// Configuration strings.
$string['apikey'] = 'API Key';
$string['apikey_help'] = 'The API key that Shake-Out provided you with.';
$string['secretkey'] = 'Secret Key';
$string['secretkey_help'] = 'The secret key that Shake-Out provided you with for webhook verification.';
$string['sandbox'] = 'Sandbox mode';
$string['sandbox_help'] = 'Use the sandbox environment for testing payments.';
$string['successurl'] = 'Success URL';
$string['successurl_help'] = 'URL to redirect users after successful payment. Leave empty to use default.';
$string['failureurl'] = 'Failure URL';
$string['failureurl_help'] = 'URL to redirect users after failed payment. Leave empty to use default.';
$string['pendingurl'] = 'Pending URL';
$string['pendingurl_help'] = 'URL to redirect users for pending payments. Leave empty to use default.';

// Payment strings.
$string['paywitshakeout'] = 'Pay with Shake-Out';
$string['paymentpending'] = 'Your payment is being processed. You will be redirected shortly.';
$string['paymentsuccessful'] = 'Payment completed successfully!';
$string['paymentfailed'] = 'Payment failed. Please try again.';
$string['paymentcancelled'] = 'Payment was cancelled.';

// Error strings.
$string['gatewaycannotbeenabled'] = 'The payment gateway cannot be enabled because the configuration is incomplete.';
$string['internalerror'] = 'An internal error occurred. Please contact the site administrator.';
$string['unknownerror'] = 'An unknown error occurred during payment processing.';
$string['invalidjson'] = 'Invalid JSON response from payment gateway.';
$string['apierror'] = 'Payment gateway API error: {$a}';
$string['curlerror'] = 'Network error: {$a}';
$string['invalidcurrency'] = 'The currency {$a} is not supported by this payment gateway.';
$string['invalidamount'] = 'Invalid payment amount.';
$string['paymentnotfound'] = 'Payment record not found.';
$string['signatureverificationfailed'] = 'Payment notification signature verification failed.';

// Privacy strings.
$string['privacy:metadata:paygw_shakeout'] = 'Stores information about Shake-Out payments.';
$string['privacy:metadata:paygw_shakeout:paymentid'] = 'The ID of the payment in Moodle.';
$string['privacy:metadata:paygw_shakeout:invoice_id'] = 'The invoice ID from Shake-Out.';
$string['privacy:metadata:paygw_shakeout:invoice_ref'] = 'The invoice reference from Shake-Out.';
$string['privacy:metadata:paygw_shakeout:invoice_url'] = 'The payment URL from Shake-Out.';
$string['privacy:metadata:paygw_shakeout:status'] = 'The status of the payment transaction.';
$string['privacy:metadata:paygw_shakeout:timecreated'] = 'The time when the payment was created.';
$string['privacy:metadata:paygw_shakeout:timemodified'] = 'The time when the payment was last modified.';

$string['privacy:metadata:shakeout'] = 'Payment data sent to Shake-Out payment gateway.';
$string['privacy:metadata:shakeout:first_name'] = 'The first name of the user making the payment.';
$string['privacy:metadata:shakeout:last_name'] = 'The last name of the user making the payment.';
$string['privacy:metadata:shakeout:email'] = 'The email address of the user making the payment.';
$string['privacy:metadata:shakeout:phone'] = 'The phone number of the user making the payment.';
$string['privacy:metadata:shakeout:address'] = 'The address of the user making the payment.';
$string['privacy:metadata:shakeout:amount'] = 'The amount of the payment.';
$string['privacy:metadata:shakeout:currency'] = 'The currency of the payment.';

// Button strings.
$string['paybutton'] = 'Pay now';
$string['processing'] = 'Processing...';
$string['redirecting'] = 'Redirecting to payment gateway...';

// Admin strings.
$string['enableshakeout'] = 'Enable Shake-Out payments';
$string['enableshakeout_desc'] = 'Enable Shake-Out as a payment method for course enrollments and other paid features.';
