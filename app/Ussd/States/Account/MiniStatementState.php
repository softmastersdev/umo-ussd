<?php

namespace App\Ussd\States\Account;

use Sparors\Ussd\Attributes\Terminate;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Menu;

#[Terminate]
class MiniStatementState implements State
{
    public function render(): Menu
    {
        // TODO: GET /api/transactions?limit=5 — fetch real transactions
        // Mock statement
        return Menu::build()
            ->line('Mini Statement')
            ->line('1. +GHS 200.00 Cash In')
            ->line('   24 Mar 2026 09:10')
            ->line('2. -GHS 50.00 Transfer')
            ->line('   23 Mar 2026 14:32')
            ->line('3. -GHS 30.00 Airtime')
            ->text('   22 Mar 2026 11:05');
    }
}
