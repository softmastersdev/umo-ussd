<?php

namespace App\Ussd\States\SendMoney;

use App\Ussd\Actions\SendMoneyAction;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class,  match: new Equal('0'))]
#[Transition(to: SendMoneyAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class ConfirmState implements State
{
    public function render(Record $record): Menu
    {
        $phone   = $record->get('recipient_phone', '');
        $name    = $record->get('recipient_name', '');
        $amount  = number_format((float) $record->get('amount', 0), 2);
        $channel = $record->get('send_channel', 'mobile_money');
        $network = $record->get('send_network', '');
        $bank    = $record->get('send_bank', '');

        $menu = Menu::build()
            ->line('Confirm Transfer')
            ->line("To: $name ($phone)");

        if ($channel === 'bank') {
            $menu->line("Bank: $bank");
        } else {
            $menu->line("Network: $network");
        }

        $menu->line("Amount: GHS $amount")
             ->line('Fee: GHS 0.00')
             ->line('--');

        if ($error = $record->get('pin_error')) {
            $menu->line($error);
        }

        return $menu->line('Enter PIN to confirm:')
                    ->text('0. Cancel');
    }

    public function setPin(Context $context, Record $record): void
    {
        $record->set('confirm_pin', $context->input());
        $record->forget('pin_error');
    }
}
