<?php

namespace App\Ussd\States;

use App\Ussd\States\Account\MenuState as AccountMenuState;
use App\Ussd\States\AtmCashOut\EnterAmountState as AtmEnterAmountState;
use App\Ussd\States\PayBill\SelectBillerState;
use App\Ussd\States\SendMoney\SelectChannelState as SendMoneySelectChannelState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Contracts\InitialState;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;

#[Transition(to: SendMoneySelectChannelState::class, match: new Equal('1'))]
#[Transition(to: SelectBillerState::class,           match: new Equal('2'))]
#[Transition(to: AtmEnterAmountState::class,         match: new Equal('3'))]
#[Transition(to: AccountMenuState::class,            match: new Equal('4'))]
#[Transition(to: ExitState::class,                   match: new Equal('0'))]
#[Transition(to: InvalidInputState::class,           match: new Fallback())]
class MainMenuState implements InitialState
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('UmoPay')
            ->line('1. Send Money')
            ->line('2. Pay Bill')
            ->line('3. ATM Cash Out')
            ->line('4. My Account')
            ->text('0. Exit');
    }
}
