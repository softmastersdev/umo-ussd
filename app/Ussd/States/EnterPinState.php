<?php

namespace App\Ussd\States;

use App\Ussd\Actions\AuthAction;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: AuthAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class EnterPinState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->text('Enter PIN:');
    }

    public function setPin(Context $context, Record $record): void
    {
        $record->set('pin', $context->input());
    }
}
