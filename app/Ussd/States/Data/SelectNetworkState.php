<?php

namespace App\Ussd\States\Data;

use App\Ussd\States\InvalidInputState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Decisions\In;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: SelectTypeState::class,   match: new Equal('0'))]
#[Transition(to: SelectBundleState::class, match: new In('1', '2', '3', '4'), callback: [self::class, 'setNetwork'])]
#[Transition(to: InvalidInputState::class, match: new Fallback())]
class SelectNetworkState implements State
{
    private array $networks = [
        '1' => 'MTN',
        '2' => 'Telecel',
        '3' => 'AirtelTigo',
        '4' => 'Glo',
    ];

    public function render(Record $record): Menu
    {
        $phone = $record->get('data_phone', '');

        return Menu::build()
            ->line('Buy Data')
            ->line("For: $phone")
            ->line('Select Network:')
            ->line('1. MTN')
            ->line('2. Telecel')
            ->line('3. AirtelTigo')
            ->line('4. Glo')
            ->text('0. Back');
    }

    public function setNetwork(Context $context, Record $record): void
    {
        $record->set('data_network', $this->networks[$context->input()] ?? 'Unknown');
    }
}
