<?php

namespace App\Ussd\States\CashOut;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class SuccessState implements State
{
    public function render(Record $record): Menu
    {
        $amount = number_format((float) $record->get('amount', 0), 2);
        $ref    = $record->get('txn_ref', 'N/A');
        $time   = $record->get('txn_time', '');

        return Menu::build()
            ->line('Cash Out Successful!')
            ->line("Withdrew GHS $amount")
            ->line("Ref: $ref")
            ->text("Time: $time");
    }
}
