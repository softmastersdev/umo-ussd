<?php

namespace App\Ussd\States;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;

#[Terminate]
class ExitState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Thank you for using UmoPay.')
            ->text('Goodbye!');
    }
}
