<?php

namespace App\Ussd\States\PayBill;

use App\Ussd\States\InvalidInputState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Decisions\In;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class,    match: new Equal('0'))]
#[Transition(to: EnterAccountState::class, match: new In('1', '2', '3', '4'), callback: [self::class, 'setBiller'])]
#[Transition(to: InvalidInputState::class, match: new Fallback())]
class SelectBillerState implements State
{
    private array $billers = [
        '1' => 'ECG (Electricity)',
        '2' => 'Ghana Water Co.',
        '3' => 'DStv',
        '4' => 'GOtv',
    ];

    public function render(): Menu
    {
        return Menu::build()
            ->line('Pay Bill')
            ->line('1. ECG (Electricity)')
            ->line('2. Ghana Water Co.')
            ->line('3. DStv')
            ->line('4. GOtv')
            ->text('0. Back');
    }

    public function setBiller(Context $context, Record $record): void
    {
        $record->set('biller_code', $context->input());
        $record->set('biller_name', $this->billers[$context->input()] ?? 'Unknown');
    }
}
