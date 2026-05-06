<?php

namespace App\Ussd\States\Airtime;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class FailedState implements State
{
    public function render(Record $record): Menu
    {
        $error = $record->get('error', 'Transaction failed.');

        return Menu::build()
            ->line('Airtime Purchase Failed')
            ->text($error);
    }
}
