<?php

namespace App\Ussd\States\SendMoney;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class SuccessState implements State
{
    public function render(Record $record): Menu
    {
        $phone   = $record->get('recipient_phone', '');
        $amount  = number_format((float) $record->get('amount', 0), 2);
        $ref     = $record->get('txn_ref', 'N/A');
        $time    = $record->get('txn_time', '');
        $channel = $record->get('send_channel', 'mobile_money');
        $network = $record->get('send_network', '');
        $bank    = $record->get('send_bank', '');
        $dest    = ($channel === 'bank') ? "$phone ($bank)" : "$phone ($network)";

        return Menu::build()
            ->line('Transfer Successful!')
            ->line("Sent GHS $amount to $dest")
            ->line("Ref: $ref")
            ->text("Time: $time");
    }
}
