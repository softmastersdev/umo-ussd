<?php

namespace App\Ussd\States\PayMerchant;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class SuccessState implements State
{
    public function render(Record $record): Menu
    {
        $merchant = $record->get('merchant_name', '');
        $amount   = number_format((float) $record->get('amount', 0), 2);
        $ref      = $record->get('txn_ref', 'N/A');

        return Menu::build()
            ->line('Payment Successful!')
            ->line("Paid GHS $amount to $merchant")
            ->text("Ref: $ref");
    }
}
