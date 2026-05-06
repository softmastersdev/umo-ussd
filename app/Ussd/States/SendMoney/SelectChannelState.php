<?php

namespace App\Ussd\States\SendMoney;

use App\Ussd\States\InvalidInputState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

// 1 = UmoPay User (internal wallet-to-wallet, instant & free)
// 2 = UmoPay Merchant (send to a registered merchant by code)
// 3 = Mobile Money (external MNO wallets: MTN/Telecel/AirtelTigo/G-Money)
// 4 = Bank Account (GhIPSS interbank transfer)
#[Transition(to: EnterPhoneState::class,            match: new Equal('1'), callback: [self::class, 'setUmoPayUser'])]
#[Transition(to: EnterUmoMerchantCodeState::class,   match: new Equal('2'))]
#[Transition(to: SelectMobileNetworkState::class,    match: new Equal('3'))]
#[Transition(to: SelectBankState::class,             match: new Equal('4'))]
#[Transition(to: MainMenuState::class,               match: new Equal('0'))]
#[Transition(to: InvalidInputState::class,           match: new Fallback())]
class SelectChannelState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Send Money')
            ->line('1. UmoPay User')
            ->line('2. UmoPay Merchant')
            ->line('3. Mobile Money')
            ->line('4. Bank Account')
            ->text('0. Back');
    }

    public function setUmoPayUser(Context $context, Record $record): void
    {
        $record->set('send_channel', 'umopay_user');
        $record->set('send_network', 'UmoPay');
    }
}
