<?php

namespace App\Ussd\States\SendMoney;

use App\Ussd\States\InvalidInputState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Decisions\In;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: SelectChannelState::class,    match: new Equal('0'))]
#[Transition(to: SelectBankState2::class,       match: new Equal('6'))]
#[Transition(to: EnterBankAccountState::class,  match: new In('1', '2', '3', '4', '5'), callback: [self::class, 'setBank'])]
#[Transition(to: InvalidInputState::class,     match: new Fallback())]
class SelectBankState implements State
{
    private array $banks = [
        '1' => 'GCB Bank',
        '2' => 'Ecobank Ghana',
        '3' => 'Fidelity Bank',
        '4' => 'Stanbic Bank',
        '5' => 'Absa Bank',
    ];

    public function render(): Menu
    {
        return Menu::build()
            ->line('Send to Bank (1/2)')
            ->line('1. GCB Bank')
            ->line('2. Ecobank Ghana')
            ->line('3. Fidelity Bank')
            ->line('4. Stanbic Bank')
            ->line('5. Absa Bank')
            ->line('6. More banks...')
            ->text('0. Back');
    }

    public function setBank(Context $context, Record $record): void
    {
        $record->set('send_bank', $this->banks[$context->input()] ?? 'Bank');
        $record->set('send_channel', 'bank');
    }
}
