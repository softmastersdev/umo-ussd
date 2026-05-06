<?php

namespace App\Ussd\States\Airtime;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class SuccessState implements State
{
    public function render(Record $record): Menu
    {
        $phone   = $record->get('airtime_phone', '');
        $network = $record->get('airtime_network', '');
        $amount  = number_format((float) $record->get('amount', 0), 2);
        $ref     = $record->get('txn_ref', 'N/A');

        return Menu::build()
            ->line('Airtime Purchase Successful!')
            ->line("GHS $amount sent to $phone ($network)")
            ->text("Ref: $ref");
    }
}
