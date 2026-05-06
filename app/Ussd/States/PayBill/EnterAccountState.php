<?php

namespace App\Ussd\States\PayBill;

use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class,   match: new Equal('0'))]
#[Transition(to: EnterAmountState::class, match: new Fallback(), callback: [self::class, 'setAccount'])]
class EnterAccountState implements State
{
    public function render(Record $record): Menu
    {
        $biller = $record->get('biller_name', 'Bill');

        return Menu::build()
            ->line("Pay $biller")
            ->line('Enter account/meter number:')
            ->text('0. Back');
    }

    public function setAccount(Context $context, Record $record): void
    {
        $record->set('account_number', $context->input());
    }
}
