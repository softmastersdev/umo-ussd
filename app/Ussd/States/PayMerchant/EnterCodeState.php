<?php

namespace App\Ussd\States\PayMerchant;

use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class,   match: new Equal('0'))]
#[Transition(to: EnterAmountState::class, match: new Fallback(), callback: [self::class, 'setCode'])]
class EnterCodeState implements State
{
    public function render(): Menu
    {
        return Menu::build()
            ->line('Pay Merchant')
            ->line('Enter merchant code:')
            ->text('0. Back');
    }

    public function setCode(Context $context, Record $record): void
    {
        $record->set('merchant_code', $context->input());
        // Mock merchant lookup — replace with API call
        $record->set('merchant_name', 'UmoPay Merchant');
    }
}
