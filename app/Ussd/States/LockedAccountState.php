<?php

namespace App\Ussd\States;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;

#[Terminate]
class LockedAccountState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Account Locked')
            ->text('Too many incorrect PINs. Try again in 24 hours.');
    }
}
