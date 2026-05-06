<?php

namespace App\Ussd\States\SendMoney;

use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: SelectBankState::class,  match: new Equal('0'))]
#[Transition(to: EnterAmountState::class, match: new Fallback(), callback: [self::class, 'setAccount'])]
class EnterBankAccountState implements State
{
    public function render(Record $record): Menu
    {
        $bank = $record->get('send_bank', 'Bank');

        return Menu::build()
            ->line("Send to $bank")
            ->line('Enter account number:')
            ->text('0. Back');
    }

    public function setAccount(Context $context, Record $record): void
    {
        $record->set('bank_account', $context->input());
        $record->set('recipient_phone', $context->input()); // shared field used by EnterAmountState
        // TODO: GhIPSS name enquiry — GET /api/banks/name-enquiry?bank=X&account=Y
        $record->set('recipient_name', 'Account Holder');
    }
}
