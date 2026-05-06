<?php

namespace App\Ussd\States\PayBill;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class SuccessState implements State
{
    public function render(Record $record): Menu
    {
        $biller = $record->get('biller_name', '');
        $amount = number_format((float) $record->get('amount', 0), 2);
        $ref    = $record->get('txn_ref', 'N/A');

        return Menu::build()
            ->line('Bill Payment Successful!')
            ->line("Paid GHS $amount to $biller")
            ->text("Ref: $ref");
    }
}
