<?php

namespace App\Ussd\Actions;

use App\Ussd\States\SendMoney\FailedState;
use App\Ussd\States\SendMoney\SuccessState;
use Sparors\Ussd\Contracts\Action;
use Sparors\Ussd\Record;

class SendMoneyAction implements Action
{
    private const MOCK_PIN = '1234';

    public function execute(Record $record): string
    {
        $pin = $record->get('confirm_pin', '');

        if ($pin !== self::MOCK_PIN) {
            $record->set('error', 'Incorrect PIN.');
            return FailedState::class;
        }

        // TODO: POST /api/transactions/transfer
        // $response = Http::withToken($record->get('jwt_token'))
        //     ->post(config('umopay.api_url') . '/transactions/transfer', [
        //         'receiverPhone' => $record->get('recipient_phone'),
        //         'amount'        => (float) $record->get('amount'),
        //         'idempotencyKey' => uniqid('txn_'),
        //     ]);

        // Mock: always succeeds when PIN is correct
        $record->set('txn_ref', strtoupper(substr(md5(uniqid()), 0, 10)));
        $record->set('txn_time', now()->format('d M Y H:i'));

        return SuccessState::class;
    }
}
