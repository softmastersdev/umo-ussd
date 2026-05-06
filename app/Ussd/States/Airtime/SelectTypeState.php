<?php

namespace App\Ussd\States\Airtime;

use App\Ussd\States\InvalidInputState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

// 1 = Top up own number — skips phone entry, goes straight to network selection
// 2 = Top up another number — enter phone first, then network
#[Transition(to: SelectNetworkState::class, match: new Equal('1'), callback: [self::class, 'setSelfPhone'])]
#[Transition(to: EnterPhoneState::class,    match: new Equal('2'))]
#[Transition(to: MainMenuState::class,    match: new Equal('0'))]
#[Transition(to: InvalidInputState::class, match: new Fallback())]
class SelectTypeState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Buy Airtime')
            ->line('1. For Myself')
            ->line('2. For Another Number')
            ->text('0. Back');
    }

    public function setSelfPhone(Context $context, Record $record): void
    {
        // Use the caller's own phone number from session context
        $record->set('airtime_phone', $context->gid());
    }
}
