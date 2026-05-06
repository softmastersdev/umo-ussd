<?php

namespace App\Ussd\States\SendMoney;

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
    public function render(Record $record): Menu
    {
        $recipient = $record->get('recipient_phone', '');
        $network   = $record->get('send_network', '');
        $bank      = $record->get('send_bank', '');
        $via       = $network ?: $bank;

        return Menu::build()
            ->line('Send Money')
            ->line("To: $recipient" . ($via ? " ($via)" : ''))
            ->line('Enter amount (GHS):')
            ->text('0. Back');
    }

    public function setAmount(Context $context, Record $record): void
    {
        $record->set('amount', $context->input());
    }
}
