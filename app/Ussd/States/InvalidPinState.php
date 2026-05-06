<?php

namespace App\Ussd\States;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;

#[Terminate]
class InvalidPinState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Incorrect PIN.')
            ->text('Please dial again to retry.');
    }
}
