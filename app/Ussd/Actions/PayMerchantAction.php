<?php

namespace App\Ussd\Actions;

use App\Ussd\States\PayMerchant\FailedState;
use App\Ussd\States\PayMerchant\SuccessState;
use Sparors\Ussd\Contracts\Action;
use Sparors\Ussd\Record;

class PayMerchantAction implements Action
{
    private const MOCK_PIN = '1234';

    public function execute(Record $record): string
    {
        $pin = $record->get('confirm_pin', '');

        if ($pin !== self::MOCK_PIN) {
            $record->set('error', 'Incorrect PIN.');
            return FailedState::class;
        }

        // TODO: POST /api/transactions/transfer (merchant payment)
        $record->set('txn_ref', strtoupper(substr(md5(uniqid()), 0, 10)));
        $record->set('txn_time', now()->format('d M Y H:i'));

        return SuccessState::class;
    }
}
