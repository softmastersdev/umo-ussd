<?php

namespace App\Ussd\States\Data;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class SuccessState implements State
{
    public function render(Record $record): Menu
    {
        $network = $record->get('data_network', '');
        $phone   = $record->get('data_phone', '');
        $bundle  = $record->get('bundle_label', '');
        $ref     = $record->get('txn_ref', 'N/A');

        return Menu::build()
            ->line('Data Purchase Successful!')
            ->line("$bundle ($network) sent to $phone")
            ->text("Ref: $ref");
    }
}
