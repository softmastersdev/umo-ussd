<?php

namespace App\Ussd\Actions;

use App\Ussd\Services\PinService;
use App\Ussd\States\AtmCashOut\ConfirmState;
use App\Ussd\States\AtmCashOut\ShowCodeState;
use App\Ussd\States\LockedAccountState;
use Sparors\Ussd\Contracts\Action;
use Sparors\Ussd\Record;

class AtmCashOutAction implements Action
{
    private const MOCK_PIN = '1234';

    public function __construct(private PinService $pins) {}

    public function execute(Record $record): string
    {
        $phone = $record->get('phone_number', '');

        if ($this->pins->isLocked($phone)) {
            return LockedAccountState::class;
        }

        $pin = $record->get('confirm_pin', '');

        if ($pin !== self::MOCK_PIN) {
            $this->pins->recordFailure($phone);

            if ($this->pins->isLocked($phone)) {
                return LockedAccountState::class;
            }

            $remaining = $this->pins->remaining($phone);
            $record->set('pin_error', "Wrong PIN. {$remaining} attempt(s) left.");

            return ConfirmState::class;
        }

        $this->pins->reset($phone);

        $code   = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = now()->addMinutes(10)->format('H:i');

        $record->set('atm_code', $code);
        $record->set('atm_expiry', $expiry);

        return ShowCodeState::class;
    }
}
