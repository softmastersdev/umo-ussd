<?php

namespace App\Ussd\Configurators;

use Illuminate\Support\Facades\Session;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\Configurator;
use Sparors\Ussd\Ussd;

/**
 * Arkesel USSD gateway configurator.
 *
 * Request params:
 *   sessionID       – unique session identifier
 *   msisdn          – caller's phone number
 *   network         – MNO identifier
 *   userData        – current screen input
 *   userID          – Arkesel internal user identifier
 *
 * Response: JSON
 *   sessionID       – echo back session ID
 *   msisdn          – echo back phone number
 *   userID          – echo back user ID
 *   message         – screen text
 *   continueSession – true = session continues, false = session ending
 *
 * NOTE: Arkesel also sends only the current screen's input (not cumulative).
 */
class Arkesel implements Configurator
{
    public function configure(Ussd $ussd): void
    {
        $id    = request('sessionID') ?? Session::getId();
        $phone = request('msisdn', '0000000000');

        $ussd->useContext(
            Context::create($id, $phone, request('userData', ''))
                ->with(['phone_number' => $phone])
        )
        ->useStore('file')
        ->useResponse(function (string $message, bool $terminating) use ($id, $phone) {
            return response()->json([
                'sessionID'       => $id,
                'msisdn'          => $phone,
                'userID'          => request('userID'),
                'message'         => $message,
                'continueSession' => !$terminating,
            ]);
        });
    }
}
