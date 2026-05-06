<?php

namespace Tests\Feature;

use App\Ussd\States\EnterPinState;
use Sparors\Ussd\Ussd;
use Tests\TestCase;

class UssdFlowTest extends TestCase
{
    // ── AUTH ──────────────────────────────────────────────────────────
    public function test_shows_pin_screen_on_first_dial(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->assertSee('Welcome to UmoPay')
            ->assertSee('Enter PIN:');
    }

    public function test_wrong_pin_terminates_session(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('9999')
            ->assertSee('Incorrect PIN.')
            ->assertSee('Please dial again');
    }

    public function test_correct_pin_shows_main_menu(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->assertSee('UmoPay')
            ->assertSee('Send Money')
            ->assertSee('Cash Out')
            ->assertSee('Pay Bill')
            ->assertSee('Buy Airtime')
            ->assertSee('ATM Cash Out')
            ->assertSee('My Account');
    }

    // ── SEND MONEY ────────────────────────────────────────────────────
    public function test_send_money_full_happy_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')        // login PIN
            ->assertSee('Send Money')
            ->input('1')           // select Send Money
            ->assertSee('Enter recipient phone')
            ->input('0501234567')  // enter phone
            ->assertSee('Enter amount')
            ->input('50')          // enter amount
            ->assertSee('Confirm Transfer')
            ->assertSee('0501234567')
            ->assertSee('50.00')
            ->input('1234')        // confirm PIN
            ->assertSee('Transfer Successful!');
    }

    public function test_send_money_wrong_confirm_pin_fails(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('1')
            ->input('0501234567')
            ->input('50')
            ->input('9999')        // wrong PIN
            ->assertSee('Transfer Failed')
            ->assertSee('Incorrect PIN.');
    }

    public function test_send_money_cancel_returns_to_main_menu(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('1')
            ->input('0501234567')
            ->input('50')
            ->input('0')           // cancel
            ->assertSee('UmoPay')  // back at main menu
            ->assertSee('Send Money');
    }

    // ── CASH OUT ──────────────────────────────────────────────────────
    public function test_cash_out_full_happy_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('2')           // Cash Out
            ->assertSee('Enter amount to withdraw')
            ->input('100')
            ->assertSee('Confirm Cash Out')
            ->assertSee('100.00')
            ->input('1234')
            ->assertSee('Cash Out Successful!');
    }

    // ── PAY BILL ──────────────────────────────────────────────────────
    public function test_pay_bill_ecg_full_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('3')           // Pay Bill
            ->assertSee('ECG')
            ->assertSee('DStv')
            ->input('1')           // ECG
            ->assertSee('Enter account')
            ->input('0012345678')  // meter number
            ->assertSee('Enter amount')
            ->input('200')
            ->assertSee('Confirm Bill Payment')
            ->assertSee('ECG')
            ->assertSee('200.00')
            ->input('1234')
            ->assertSee('Bill Payment Successful!');
    }

    // ── PAY MERCHANT ──────────────────────────────────────────────────
    public function test_pay_merchant_full_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('4')           // Pay Merchant
            ->assertSee('Enter merchant code')
            ->input('MCH001')
            ->assertSee('Enter amount')
            ->input('75')
            ->assertSee('Confirm Merchant Payment')
            ->assertSee('MCH001')
            ->assertSee('75.00')
            ->input('1234')
            ->assertSee('Payment Successful!');
    }

    // ── AIRTIME ───────────────────────────────────────────────────────
    public function test_airtime_for_self_full_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->actingAs('0244123456')
            ->start()
            ->input('1234')
            ->input('5')           // Buy Airtime
            ->assertSee('For Myself')
            ->assertSee('For Another Number')
            ->input('1')           // Self
            ->assertSee('Enter amount') // skips EnterPhone
            ->input('10')
            ->assertSee('Confirm Airtime')
            ->input('1234')
            ->assertSee('Airtime Purchase Successful!');
    }

    public function test_airtime_for_other_full_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('5')
            ->input('2')           // For Another
            ->assertSee('Enter recipient phone')
            ->input('0271111111')
            ->assertSee('Enter amount')
            ->input('5')
            ->assertSee('Confirm Airtime')
            ->assertSee('0271111111')
            ->input('1234')
            ->assertSee('Airtime Purchase Successful!');
    }

    // ── DATA ──────────────────────────────────────────────────────────
    public function test_data_bundle_full_path(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('6')           // Buy Data
            ->assertSee('MTN')
            ->assertSee('AirtelTigo')
            ->input('1')           // MTN
            ->assertSee('Enter phone')
            ->input('0241234567')
            ->assertSee('1GB')
            ->assertSee('5GB')
            ->input('2')           // 2GB bundle
            ->assertSee('Confirm Data Purchase')
            ->assertSee('2GB')
            ->assertSee('9.00')
            ->input('1234')
            ->assertSee('Data Purchase Successful!');
    }

    // ── ATM CASH OUT ──────────────────────────────────────────────────
    public function test_atm_cashout_generates_code(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('7')           // ATM Cash Out
            ->assertSee('Enter withdrawal amount')
            ->input('500')
            ->assertSee('ATM Cash Out')
            ->assertSee('500.00')
            ->input('1234')
            ->assertSee('ATM Cash Out Code:')
            ->assertSee('GHS 500.00')
            ->assertSee('Expires:');
    }

    // ── MY ACCOUNT ────────────────────────────────────────────────────
    public function test_account_balance(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('8')           // My Account
            ->assertSee('Check Balance')
            ->assertSee('Mini Statement')
            ->input('1')           // Balance
            ->assertSee('Account Balance')
            ->assertSee('GHS');
    }

    public function test_account_mini_statement(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('8')
            ->input('2')           // Mini Statement
            ->assertSee('Mini Statement')
            ->assertSee('Cash In')
            ->assertSee('Transfer');
    }

    // ── EXIT ──────────────────────────────────────────────────────────
    public function test_exit_ends_session(): void
    {
        Ussd::test(EnterPinState::class)
            ->start()
            ->input('1234')
            ->input('0')           // Exit
            ->assertSee('Thank you for using UmoPay')
            ->assertSee('Goodbye');
    }
}
