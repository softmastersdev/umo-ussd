<?php

namespace App\Ussd\States\CashOut;

use App\Ussd\Actions\CashOutAction;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class, match: new Equal('0'))]
#[Transition(to: CashOutAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class ConfirmState implements State
{
    public function render(Record $record): Menu
    {
        $amount = number_format((float) $record->get('amount', 0), 2);

        return Menu::build()
            ->line('Confirm Cash Out')
            ->line("Amount: GHS $amount")
            ->line('--')
            ->line('Enter PIN to confirm:')
            ->text('0. Cancel');
    }

    public function setPin(Context $context, Record $record): void
    {
        $record->set('confirm_pin', $context->input());
    }
}
