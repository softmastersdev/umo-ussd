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

#[Transition(to: SelectChannelState::class, match: new Equal('0'))]
#[Transition(to: EnterPhoneState::class,    match: new In('1', '2', '3', '4'), callback: [self::class, 'setNetwork'])]
#[Transition(to: InvalidInputState::class,  match: new Fallback())]
class SelectMobileNetworkState implements State
{
    private array $networks = [
        '1' => 'MTN MoMo',
        '2' => 'Telecel Cash',
        '3' => 'AirtelTigo Money',
        '4' => 'G-Money',
    ];

    public function render(): Menu
    {
        return Menu::build()
            ->line('Send Money')
            ->line('Select Network:')
            ->line('1. MTN MoMo')
            ->line('2. Telecel Cash')
            ->line('3. AirtelTigo Money')
            ->line('4. G-Money')
            ->text('0. Back');
    }

    public function setNetwork(Context $context, Record $record): void
    {
        $record->set('send_network', $this->networks[$context->input()] ?? 'Mobile Money');
        $record->set('send_channel', 'mobile_money');
    }
}
