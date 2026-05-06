<?php

namespace App\Ussd\States\AtmCashOut;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class ShowCodeState implements State
{
    public function render(Record $record): Menu
    {
        $amount = number_format((float) $record->get('amount', 0), 2);
        $code   = $record->get('atm_code', '------');
        $expiry = $record->get('atm_expiry', '');

        return Menu::build()
            ->line('ATM Cash Out Code:')
            ->line("*** $code ***")
            ->line("Amount: GHS $amount")
            ->line("Expires: $expiry")
            ->text('Visit any partner ATM to withdraw.');
    }
}
