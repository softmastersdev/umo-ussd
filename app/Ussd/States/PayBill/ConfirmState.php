<?php

namespace App\Ussd\States\PayBill;

use App\Ussd\Actions\PayBillAction;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class, match: new Equal('0'))]
#[Transition(to: PayBillAction::class, match: new Fallback(), callback: [self::class, 'setPin'])]
class ConfirmState implements State
{
    public function render(Record $record): Menu
    {
        $biller  = $record->get('biller_name', '');
        $account = $record->get('account_number', '');
        $amount  = number_format((float) $record->get('amount', 0), 2);

        $menu = Menu::build()
            ->line('Confirm Bill Payment')
            ->line("Biller: $biller")
            ->line("Account: $account")
            ->line("Amount: GHS $amount")
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
