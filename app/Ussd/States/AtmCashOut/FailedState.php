<?php

namespace App\Ussd\States\AtmCashOut;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Terminate]
class FailedState implements State
{
    public function render(Record $record): Menu
    {
        $error = $record->get('error', 'Could not generate code.');

        return Menu::build()
            ->line('ATM Cash Out Failed')
            ->text($error);
    }
}
