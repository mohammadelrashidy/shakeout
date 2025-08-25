
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
 * Shake-Out payment gateway modal
 *
 * @package     paygw_shakeout
 * @copyright   2025 Mohammad Nabil <mohammad@smartlearn.education>  
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';

export const process = (component, paymentArea, itemId, description) => {
    return new Promise((resolve, reject) => {
        const params = new URLSearchParams({
            component: component,
            paymentarea: paymentArea,
            itemid: itemId,
            description: description
        });
        
        const url = M.cfg.wwwroot + '/payment/gateway/shakeout/pay.php?' + params.toString();
        
        // Redirect to payment page
        window.location.href = url;
    });
};
