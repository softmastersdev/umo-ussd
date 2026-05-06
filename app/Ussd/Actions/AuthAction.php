<?php

namespace App\Ussd\Actions;

use App\Ussd\States\InvalidPinState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Contracts\Action;
use Sparors\Ussd\Record;

class AuthAction implements Action
{
    private const MOCK_PIN = '1234';

    public function execute(Record $record): string
    {
        $pin = $record->get('pin', '');

        if ($pin !== self::MOCK_PIN) {
            return InvalidPinState::class;
        }

        return $record->get('pin_destination', MainMenuState::class);
    }
}
