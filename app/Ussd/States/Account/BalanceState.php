<?php

namespace App\Ussd\States\Account;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;

#[Terminate]
class BalanceState implements State
{
    public function render(Context $context): Menu
    {
        $phone = $context->gid();

        // TODO: GET /api/wallets — fetch real balance
        // Mock balance
        $balance   = number_format(500.00, 2);
        $ledger    = number_format(500.00, 2);
        $timestamp = now()->format('d M Y H:i');

        return Menu::build()
            ->line('Account Balance')
            ->line("Phone: $phone")
            ->line("Available: GHS $balance")
            ->line("Ledger:    GHS $ledger")
            ->text("As at: $timestamp");
    }
}
