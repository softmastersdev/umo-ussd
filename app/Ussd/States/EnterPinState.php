<?php

namespace App\Ussd\States;

use App\Ussd\Actions\AuthAction;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\InitialState;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: AuthAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class EnterPinState implements InitialState
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Welcome to UmoPay')
            ->line('Your mobile money wallet')
            ->text('Enter PIN:');
    }

    public function setPin(Context $context, Record $record): void
    {
        $record->set('pin', $context->input());
    }
}
