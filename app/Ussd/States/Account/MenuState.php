<?php

namespace App\Ussd\States\Account;

use App\Ussd\States\InvalidInputState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;

#[Transition(to: BalanceState::class,         match: new Equal('1'))]
#[Transition(to: MiniStatementState::class,   match: new Equal('2'))]
#[Transition(to: MainMenuState::class,         match: new Equal('0'))]
#[Transition(to: InvalidInputState::class,    match: new Fallback())]
class MenuState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('My Account')
            ->line('1. Check Balance')
            ->line('2. Mini Statement')
            ->text('0. Back');
    }
}
