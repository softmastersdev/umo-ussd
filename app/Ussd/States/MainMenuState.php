<?php

namespace App\Ussd\States;

use App\Ussd\States\Account\MenuState as AccountMenuState;
use App\Ussd\States\Airtime\SelectTypeState as AirtimeSelectTypeState;
use App\Ussd\States\AtmCashOut\EnterAmountState as AtmEnterAmountState;
use App\Ussd\States\CashOut\EnterAmountState as CashOutEnterAmountState;
use App\Ussd\States\Data\SelectTypeState as DataSelectTypeState;
use App\Ussd\States\PayBill\SelectBillerState;
use App\Ussd\States\PayMerchant\EnterCodeState as MerchantEnterCodeState;
use App\Ussd\States\SendMoney\SelectChannelState as SendMoneySelectChannelState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: SendMoneySelectChannelState::class, match: new Equal('1'))]
#[Transition(to: CashOutEnterAmountState::class,  match: new Equal('2'))]
#[Transition(to: SelectBillerState::class,         match: new Equal('3'))]
#[Transition(to: MerchantEnterCodeState::class,    match: new Equal('4'))]
#[Transition(to: AirtimeSelectTypeState::class,    match: new Equal('5'))]
#[Transition(to: DataSelectTypeState::class,       match: new Equal('6'))]
#[Transition(to: AtmEnterAmountState::class,       match: new Equal('7'))]
#[Transition(to: AccountMenuState::class,          match: new Equal('8'))]
#[Transition(to: ExitState::class,                 match: new Equal('0'))]
#[Transition(to: InvalidInputState::class,         match: new Fallback())]
class MainMenuState implements State
{
    public function render(Record $record): Menu
    {
        $phone = $record->get('phone_number', 'Customer');

        return Menu::build()
            ->line("UmoPay - $phone")
            ->line('1. Send Money')
            ->line('2. Cash Out')
            ->line('3. Pay Bill')
            ->line('4. Pay Merchant')
            ->line('5. Buy Airtime')
            ->line('6. Buy Data')
            ->line('7. ATM Cash Out')
            ->line('8. My Account')
            ->text('0. Exit');
    }
}
