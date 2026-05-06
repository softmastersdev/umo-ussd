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

#[Transition(to: SelectBankState::class,       match: new Equal('0'))]
#[Transition(to: EnterBankAccountState::class,  match: new In('1', '2', '3', '4', '5'), callback: [self::class, 'setBank'])]
#[Transition(to: InvalidInputState::class,     match: new Fallback())]
class SelectBankState2 implements State
{
    private array $banks = [
        '1' => 'Access Bank',
        '2' => 'CAL Bank',
        '3' => 'Republic Bank',
        '4' => 'UBA Ghana',
        '5' => 'Zenith Bank',
    ];

    public function render(): Menu
    {
        return Menu::build()
            ->line('Send to Bank (2/2)')
            ->line('1. Access Bank')
            ->line('2. CAL Bank')
            ->line('3. Republic Bank')
            ->line('4. UBA Ghana')
            ->line('5. Zenith Bank')
            ->text('0. Back');
    }

    public function setBank(Context $context, Record $record): void
    {
        $record->set('send_bank', $this->banks[$context->input()] ?? 'Bank');
        $record->set('send_channel', 'bank');
    }
}
