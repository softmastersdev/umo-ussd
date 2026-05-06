<?php

namespace App\Ussd\States\AtmCashOut;

use App\Ussd\Actions\AtmCashOutAction;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class,   match: new Equal('0'))]
#[Transition(to: AtmCashOutAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class ConfirmState implements State
{
    public function render(Record $record): Menu
    {
        $amount = number_format((float) $record->get('amount', 0), 2);

        return Menu::build()
            ->line('ATM Cash Out')
            ->line("Amount: GHS $amount")
            ->line('A one-time code will be sent.')
            ->line('Use at any partner ATM.')
            ->line('--')
            ->line('Enter PIN to confirm:')
            ->text('0. Cancel');
    }

    public function setPin(Context $context, Record $record): void
    {
        $record->set('confirm_pin', $context->input());
    }
}
