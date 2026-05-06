<?php

namespace App\Ussd\Configurators;

use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\Configurator;
use Sparors\Ussd\Ussd;

/**
 * Africa's Talking USSD gateway configurator.
 *
 * Request params:
 *   sessionId   – unique session identifier
 *   phoneNumber – caller's MSISDN (e.g. +233244123456)
 *   networkCode – MNO code (e.g. MTN, Tigo, Airtel, Vodafone)
 *   text        – cumulative input separated by * (e.g. "1*2*50")
 *
 * Response: text/plain prefixed with CON (continue) or END (terminate)
 */
class AfricasTalking implements Configurator
{
    public function configure(Ussd $ussd): void
    {
        $text  = request('text', '');
        $parts = strlen($text) > 0 ? explode('*', $text) : [''];
        $input = end($parts);

        $phone   = request('phoneNumber', '0000000000');
        $session = request('sessionId', uniqid());

        $ussd->useContext(
            Context::create($session, $phone, $input)
                ->with(['phone_number' => $phone])
        )
        ->useStore('file')
        ->useResponse(function (string $message, bool $terminating) {
            $prefix = $terminating ? 'END' : 'CON';

            return response("$prefix $message", 200)
                ->header('Content-Type', 'text/plain');
        });
    }
}
