<?php

namespace App\Ussd\States\SendMoney;

use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: SelectChannelState::class, match: new Equal('0'))]
#[Transition(to: EnterAmountState::class,   match: new Fallback(), callback: [self::class, 'setMerchant'])]
class EnterUmoMerchantCodeState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Send to UmoPay Merchant')
            ->line('Enter merchant code:')
            ->text('0. Back');
    }

    public function setMerchant(Context $context, Record $record): void
    {
        $code = $context->input();

        $record->set('send_channel', 'umopay_merchant');
        $record->set('send_network', 'UmoPay Merchant');
        $record->set('recipient_phone', $code);
        // TODO: GET /api/merchants/{code} — fetch real merchant name
        $record->set('recipient_name', 'UmoPay Merchant');
    }
}
