<?php

namespace App\Ussd\States\Airtime;

use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: SelectTypeState::class,    match: new Equal('0'))]
#[Transition(to: SelectNetworkState::class, match: new Fallback(), callback: [self::class, 'setPhone'])]
class EnterPhoneState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Buy Airtime')
            ->line('Enter recipient phone number:')
            ->text('0. Back');
    }

    public function setPhone(Context $context, Record $record): void
    {
        $record->set('airtime_phone', $context->input());
    }
}
