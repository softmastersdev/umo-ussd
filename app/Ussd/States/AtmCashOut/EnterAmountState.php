<?php

namespace App\Ussd\States\AtmCashOut;

use App\Ussd\States\InvalidInputState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Decisions\IsNumeric;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class, match: new Equal('0'))]
#[Transition(to: ConfirmState::class,  match: new IsNumeric(), callback: [self::class, 'setAmount'])]
#[Transition(to: InvalidInputState::class, match: new Fallback())]
class EnterAmountState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('ATM Cash Out')
            ->line('Enter withdrawal amount (GHS):')
            ->line('A 6-digit code will be generated.')
            ->text('0. Back');
    }

    public function setAmount(Context $context, Record $record): void
    {
        $record->set('amount', $context->input());
    }
}
