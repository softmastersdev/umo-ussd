<?php

namespace App\Ussd\States;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;

#[Terminate]
class InvalidInputState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Invalid input.')
            ->text('Please dial again.');
    }
}
