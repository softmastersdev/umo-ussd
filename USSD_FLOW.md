# UmoPay USSD Flow — Arkesel Gateway

## How It Works

The subscriber dials the UmoPay short code. Arkesel forwards each keypress to the server and displays the response on the subscriber's screen. The session ends when `continueSession` is `false`.

---

## Main Menu

```
Dial *XXX#
     │
     ▼
┌─────────────────┐
│     UmoPay      │
│ 1. Send Money   │
│ 2. Pay Bill     │
│ 3. ATM Cash Out │
│ 4. My Account   │
│ 0. Exit         │
└─────────────────┘
     │
  ┌──┴──────────┬──────────────┬──────────┐
  1             2              3          4
  ▼             ▼              ▼          ▼
Send Money   Pay Bill    ATM Cash Out  My Account
```

---

## 1. Send Money

### Choose a channel

```
┌──────────────────────┐
│ Send Money           │
│ 1. UmoPay User       │
│ 2. UmoPay Merchant   │
│ 3. Mobile Money      │
│ 4. Bank Account      │
│ 0. Back              │
└──────────────────────┘
```

---

### 1a — To a UmoPay User

```
Enter recipient phone number
          │
          ▼
Enter amount (GHS)
          │
          ▼
Confirm screen
  To: <name> (<phone>)
  Network: UmoPay
  Amount: GHS X.XX
  Fee: GHS 0.00
  Enter PIN to confirm / 0. Cancel
          │
          ▼
     PIN check
    ┌────┴────┐
 Correct    Wrong
    │           │ (up to 3 tries)
    ▼           ▼
 SUCCESS     Retry with remaining attempts shown
                  │ (3rd wrong attempt)
                  ▼
             ACCOUNT LOCKED
```

---

### 1b — To a UmoPay Merchant

```
Enter merchant code
          │
          ▼
Enter amount (GHS)
          │
          ▼
Confirm → PIN check → SUCCESS / LOCKED
```

---

### 1c — Mobile Money

```
Select network:
  1. MTN MoMo
  2. Telecel Cash
  3. AirtelTigo Money
  4. G-Money
          │
          ▼
Enter recipient phone number
          │
          ▼
Enter amount (GHS)
          │
          ▼
Confirm → PIN check → SUCCESS / LOCKED
```

---

### 1d — Bank Account

```
Select bank (page 1 of 2):
  1. GCB Bank
  2. Ecobank Ghana
  3. Fidelity Bank
  4. Stanbic Bank
  5. Absa Bank
  6. More banks...
          │ (6 → page 2)
          ▼
Select bank (page 2 of 2):
  1. Access Bank
  2. CAL Bank
  3. Republic Bank
  4. UBA Ghana
  5. Zenith Bank
  0. Back
          │
          ▼
Enter account number
          │
          ▼
Enter amount (GHS)
          │
          ▼
Confirm screen
  To: <name> (<account>)
  Bank: <selected bank>
  Amount: GHS X.XX
  Fee: GHS 0.00
  Enter PIN to confirm / 0. Cancel
          │
          ▼
PIN check → SUCCESS / LOCKED
```

---

### Send Money — End Screens

```
SUCCESS
┌────────────────────────────────┐
│ Transfer Successful!           │
│ Sent GHS X.XX to               │
│   <phone/account> (<channel>)  │
│ Ref: XXXXXXXXXX                │
│ Time: DD Mon YYYY HH:MM        │
└────────────────────────────────┘

FAILED
┌──────────────────────┐
│ Transfer Failed      │
│ <reason>             │
└──────────────────────┘
```

---

## 2. Pay Bill

```
Select biller:
  1. ECG (Electricity)
  2. Ghana Water Co.
  3. DStv
  4. GOtv
  0. Back
          │
          ▼
Enter account / meter number
          │
          ▼
Enter amount (GHS)
          │
          ▼
Confirm screen
  Biller: <biller>
  Account: <account>
  Amount: GHS X.XX
  Enter PIN to confirm / 0. Cancel
          │
          ▼
PIN check → SUCCESS / LOCKED
```

### Pay Bill — End Screens

```
SUCCESS
┌──────────────────────────────┐
│ Bill Payment Successful!     │
│ Paid GHS X.XX to <biller>    │
│ Ref: XXXXXXXXXX              │
└──────────────────────────────┘

FAILED
┌──────────────────────────────┐
│ Bill Payment Failed          │
│ <reason>                     │
└──────────────────────────────┘
```

---

## 3. ATM Cash Out

```
Enter withdrawal amount (GHS)
  (A 6-digit code will be generated)
          │
          ▼
Confirm screen
  Amount: GHS X.XX
  A one-time code will be sent.
  Use at any partner ATM.
  Enter PIN to confirm / 0. Cancel
          │
          ▼
PIN check
    ┌─────┴─────┐
 Correct      Wrong → Retry / LOCKED
    │
    ▼
 CODE SCREEN (END)
┌──────────────────────────────────────┐
│ ATM Cash Out Code:                   │
│ *** 6-digit code ***                 │
│ Amount: GHS X.XX                     │
│ Expires: HH:MM  (valid 10 minutes)   │
│ Visit any partner ATM to withdraw.   │
└──────────────────────────────────────┘
```

---

## 4. My Account

```
┌──────────────────────┐
│ My Account           │
│ 1. Check Balance     │
│ 2. Mini Statement    │
│ 0. Back              │
└──────────────────────┘
      │          │
      1          2
      ▼          ▼

BALANCE (END)               MINI STATEMENT (END)
┌──────────────────────┐    ┌──────────────────────────┐
│ Account Balance      │    │ Mini Statement            │
│ Phone: <number>      │    │ 1. +GHS X.XX  Cash In    │
│ Available: GHS X.XX  │    │    <date>                 │
│ Ledger:    GHS X.XX  │    │ 2. -GHS X.XX  Transfer   │
│ As at: <timestamp>   │    │    <date>                 │
└──────────────────────┘    │ 3. -GHS X.XX  Airtime    │
                            │    <date>                 │
                            └──────────────────────────┘
```

---

## PIN Security

All transactions require a PIN before processing.

| Scenario                    | What happens                                      |
|-----------------------------|---------------------------------------------------|
| Wrong PIN (1st or 2nd try)  | Error shown with attempts remaining. Try again.   |
| Wrong PIN (3rd try)         | Account locked for 24 hours.                      |
| Correct PIN                 | Transaction proceeds.                             |
| Already locked              | Locked message shown immediately.                 |

```
ACCOUNT LOCKED (END)
┌────────────────────────────────────────────────────┐
│ Account Locked                                     │
│ Too many incorrect PINs. Try again in 24 hours.   │
└────────────────────────────────────────────────────┘
```

---

## Other End Screens

```
EXIT (0 at Main Menu)
┌────────────────────────────┐
│ Thank you for using UmoPay.│
│ Goodbye!                   │
└────────────────────────────┘

INVALID INPUT
┌─────────────────────────┐
│ Invalid input.          │
│ Please dial again.      │
└─────────────────────────┘
```
