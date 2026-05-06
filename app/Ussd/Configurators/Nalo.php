<?php

namespace App\Ussd\Configurators;

use Illuminate\Support\Facades\Session;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\Configurator;
use Sparors\Ussd\Ussd;

/**
 * Nalo Solutions USSD gateway configurator.
 *
 * Request params:
 *   SESSIONID – unique session identifier
 *   MSISDN    – caller's phone number
 *   NETWORK   – MNO identifier
 *   USERDATA  – current screen input (Nalo sends only the latest input, not cumulative)
 *   USERID    – Nalo internal user identifier
 *
 * Response: JSON
 *   MSISDN   – echo back phone number
 *   MSG      – screen text
 *   USERDATA – echo back input
 *   MSGTYPE  – true = continue (expect input), false = terminate session
 *   USERID   – echo back user ID
 *
 * NOTE: Unlike Africa's Talking, Nalo sends only the current screen's input
 * in USERDATA (not cumulative), so no * splitting is needed.
 */
class Nalo implements Configurator
{
    public function configure(Ussd $ussd): void
    {
        $id    = request('SESSIONID') ?? Session::getId();
        $phone = request('MSISDN', '0000000000');

        $ussd->useContext(
            Context::create($id, $phone, request('USERDATA', ''))
                ->with(['phone_number' => $phone])
        )
        ->useStore('file')
        ->useResponse(function (string $message, bool $terminating) use ($phone) {
            return response()->json([
                'MSISDN'   => $phone,
                'MSG'      => $message,
                'USERDATA' => request('USERDATA'),
                'MSGTYPE'  => !$terminating,
                'USERID'   => request('USERID'),
            ]);
        });
    }
}
