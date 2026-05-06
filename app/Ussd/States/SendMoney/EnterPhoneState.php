<?php

namespace App\Ussd\States\SendMoney;

use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

// Back returns to channel selection regardless of whether caller chose UmoPay User or Mobile Money
#[Transition(to: SelectChannelState::class, match: new Equal('0'))]
#[Transition(to: EnterAmountState::class,         match: new Fallback(), callback: [self::class, 'setPhone'])]
class EnterPhoneState implements State
{
    public function render(Record $record): Menu
    {
        $network = $record->get('send_network', 'Mobile Money');

        return Menu::build()
            ->line("Send Money ($network)")
            ->line('Enter recipient phone number:')
            ->text('0. Back');
    }

    public function setPhone(Context $context, Record $record): void
    {
        $record->set('recipient_phone', $context->input());
        // TODO: lookup recipient name via API — GET /api/users/lookup?phone=X
        $record->set('recipient_name', 'UmoPay User');
    }
}
