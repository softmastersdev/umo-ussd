<?php

namespace App\Ussd\Actions;

use App\Ussd\States\AtmCashOut\FailedState;
use App\Ussd\States\AtmCashOut\ShowCodeState;
use Sparors\Ussd\Contracts\Action;
use Sparors\Ussd\Record;

class AtmCashOutAction implements Action
{
    private const MOCK_PIN = '1234';

    public function execute(Record $record): string
    {
        $pin = $record->get('confirm_pin', '');

        if ($pin !== self::MOCK_PIN) {
            $record->set('error', 'Incorrect PIN.');
            return FailedState::class;
        }

        // Generate a 6-digit ATM withdrawal code (valid for 10 minutes)
        // TODO: store this code in the backend with expiry linked to the user's wallet
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = now()->addMinutes(10)->format('H:i');

        $record->set('atm_code', $code);
        $record->set('atm_expiry', $expiry);

        return ShowCodeState::class;
    }
}
