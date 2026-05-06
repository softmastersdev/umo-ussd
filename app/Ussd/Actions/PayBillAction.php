<?php

namespace App\Ussd\Actions;

use App\Ussd\Services\PinService;
use App\Ussd\States\LockedAccountState;
use App\Ussd\States\PayBill\ConfirmState;
use App\Ussd\States\PayBill\SuccessState;
use Sparors\Ussd\Contracts\Action;
use Sparors\Ussd\Record;

class PayBillAction implements Action
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

        // TODO: POST /api/transactions/transfer (PAYMENT type)
        $record->set('txn_ref', strtoupper(substr(md5(uniqid()), 0, 10)));
        $record->set('txn_time', now()->format('d M Y H:i'));

        return SuccessState::class;
    }
}
