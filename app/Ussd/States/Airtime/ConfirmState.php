<?php

namespace App\Ussd\States\Airtime;

use App\Ussd\Actions\BuyAirtimeAction;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class,   match: new Equal('0'))]
#[Transition(to: BuyAirtimeAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class ConfirmState implements State
{
    public function render(Record $record): Menu
    {
        $phone   = $record->get('airtime_phone', '');
        $network = $record->get('airtime_network', '');
        $amount  = number_format((float) $record->get('amount', 0), 2);

        return Menu::build()
            ->line('Confirm Airtime Purchase')
            ->line("To: $phone")
            ->line("Network: $network")
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
