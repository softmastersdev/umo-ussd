<?php

namespace App\Ussd\Responses;

use Sparors\Ussd\Contracts\Response;

class AfricasTalkingResponse implements Response
{
    public function respond(string $message, bool $terminating): mixed
    {
        $prefix = $terminating ? 'END' : 'CON';
        return response("$prefix $message", 200)->header('Content-Type', 'text/plain');
    }
}
