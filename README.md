# UmoPay USSD — Customer Flow & Technical Walkthrough

> Africa's Talking · Nalo Solutions · Arkesel · Laravel · `sparors/laravel-ussd` v3
> Dial code: `*XXX#` (configure per gateway dashboard)

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Architecture at a Glance](#2-architecture-at-a-glance)
3. [Multi-Gateway Support](#3-multi-gateway-support)
4. [How Each Gateway Handles Input](#4-how-each-gateway-handles-input)
5. [First-Time Customer Flow](#5-first-time-customer-flow)
6. [Returning Customer — Full Walkthrough](#6-returning-customer--full-walkthrough)
   - [6.1 Entry & PIN Authentication](#61-entry--pin-authentication)
   - [6.2 Main Menu](#62-main-menu)
   - [6.3 Send Money](#63-1--send-money)
   - [6.4 Cash Out](#64-2--cash-out)
   - [6.5 Pay Bill](#65-3--pay-bill)
   - [6.6 Pay Merchant](#66-4--pay-merchant)
   - [6.7 Buy Airtime](#67-5--buy-airtime)
   - [6.8 Buy Data](#68-6--buy-data)
   - [6.9 ATM Cash Out](#69-7--atm-cash-out)
   - [6.10 My Account](#610-8--my-account)
7. [Error & Dead-End States](#7-error--dead-end-states)
8. [Session Data Reference](#8-session-data-reference)
9. [Current Mock Values (Dev)](#9-current-mock-values-dev)
10. [Gaps & Recommended Improvements](#10-gaps--recommended-improvements)

---

## 1. System Overview

UmoPay USSD is a mobile-money wallet accessible from any phone — no smartphone or data connection required. Customers interact through a series of numbered menus delivered over the GSM USSD protocol.

The application supports **multiple USSD gateway providers simultaneously**. Each gateway posts to its own endpoint; a dedicated Configurator class translates that gateway's request/response format into the shared state machine.

Key characteristics:
- Every session starts fresh from **PIN entry** — there is no persistent "logged in" state between sessions.
- All screens are plain text, max ~182 characters per response.
- `CON` responses continue the session; `END` responses terminate it (Africa's Talking format).
- The `0` key universally navigates **back** or **cancels** at any screen.
- Every money movement requires **PIN confirmation** as a second factor.

---

## 2. Architecture at a Glance

```
HTTP POST (any gateway)
        │
        ▼
UssdController::africasTalking()          → AfricasTalking configurator
UssdController::nalo()                    → Nalo configurator
UssdController::arkesel()                 → Arkesel configurator
        │
        ▼  each configurator calls:
        │    ussd->useContext(Context::create(...))
        │    ussd->useStore('file')
        │    ussd->useResponse(fn ...)
        ▼
sparors/ussd engine     ← manages session state in file cache keyed by sessionId
        │
        ├── States/       ← render screen + declare transitions
        ├── Actions/      ← execute business logic, return next state class
        └── Configurators/ ← gateway-specific input parsing + response formatting
```

**State machine rules:**

| Concept | Implementation |
|---|---|
| Entry point | `EnterPinState` (`InitialState`) |
| Continuing screen | `CON <text>` (AT) / `MSGTYPE: false` (Nalo) / `continueSession: true` (Arkesel) |
| Terminal screen | `#[Terminate]` attribute |
| Routing | `#[Transition(to: X, match: new Equal('1'))]` PHP 8 attributes |
| Fallback | `new Fallback()` — catches anything not matched above it |
| Numeric guard | `new IsNumeric()` — rejects letters/symbols for amount fields |

---

## 3. Multi-Gateway Support

The project uses `sparors/laravel-ussd` v3's **Configurator pattern**. Each gateway gets:
- Its own route endpoint
- Its own Configurator class that parses that gateway's request params and formats the response

All three can run **simultaneously** — sessions are isolated by the `sessionId` each gateway provides.

| Gateway | Endpoint | Configurator | Response format |
|---|---|---|---|
| Africa's Talking | `POST /ussd/at` | `AfricasTalking` | `text/plain` `CON`/`END` prefix |
| Nalo Solutions | `POST /ussd/nalo` | `Nalo` | JSON `{MSISDN, MSG, MSGTYPE, ...}` |
| Arkesel | `POST /ussd/arkesel` | `Arkesel` | JSON `{sessionID, message, continueSession, ...}` |

The backward-compatible `POST /ussd` alias maps to Africa's Talking.

### Adding a new gateway

```bash
php artisan make:ussd:configurator Nsano
```

Then implement `configure(Ussd $ussd): void` — call `useContext()`, `useStore()`, and `useResponse()` — and add a route + controller method.

**Important:** In this v3-beta install, `Configurator::configure()` receives `Ussd $ussd` (not `Machine $machine`). The available builder methods on `$ussd` are:

| Method | Purpose |
|---|---|
| `useContext(Context $ctx)` | Set session ID, phone, current input, extra data |
| `useStore(string $name)` | Session store: `'file'`, `'database'`, `'array'` |
| `useResponse(Closure\|Response)` | Format and return the HTTP response |
| `useInitialState(string $class)` | Override the entry state (usually set in controller) |
| `useExceptionHandler(...)` | Custom error handling |

---

## 4. How Each Gateway Handles Input

| Gateway | Session param | Phone param | Input param | Cumulative? |
|---|---|---|---|---|
| Africa's Talking | `sessionId` | `phoneNumber` | `text` (split on `*`, take last) | Yes — split needed |
| Nalo Solutions | `SESSIONID` | `MSISDN` | `USERDATA` | No — current screen only |
| Arkesel | `sessionID` | `msisdn` | `userData` | No — current screen only |

Africa's Talking example: customer typed `1`, then `0244123456`, then `50`
→ AT sends `text = "1*0244123456*50"`
→ Configurator extracts last segment: `50`

---

## 4. First-Time Customer Flow

> **Current state:** The USSD application does not yet have a self-service registration flow. A new customer must be **onboarded via another channel** (web/app/agent) before their PIN is active.

When a first-time customer dials without a pre-registered account:

```
Screen 1 — Entry
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CON Welcome to UmoPay
    Your mobile money wallet
    Enter PIN:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Customer types any PIN → no account found

Screen 2 — Terminal
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
END Incorrect PIN.
    Please dial again to retry.
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

**Recommended onboarding journey** (see [Section 9.1](#91-missing-self-service-registration) for full spec):
`Dial code → No account found → Offer "1. Register" / "2. I have an account"` → collect name, NIA ID, set PIN → wallet created.

---

## 5. Returning Customer — Full Walkthrough

### 5.1 Entry & PIN Authentication

Customer dials `*XXX#`.

```
┌─────────────────────────────────────┐
│ CON Welcome to UmoPay               │
│     Your mobile money wallet        │
│     Enter PIN:                      │
└─────────────────────────────────────┘
         │
         │  [any input]
         ▼
     AuthAction
    ┌────┴────┐
    │         │
 PIN OK    PIN wrong
    │         │
    ▼         ▼
MainMenu  END Incorrect PIN.
              Please dial again to retry.
```

**Session record set:** `pin` = customer input
**Decision:** hardcoded `MOCK_PIN = '1234'` today → replace with API credential check.

---

### 5.2 Main Menu

```
┌─────────────────────────────────────┐
│ CON UmoPay - 0244123456             │
│     1. Send Money                   │
│     2. Cash Out                     │
│     3. Pay Bill                     │
│     4. Pay Merchant                 │
│     5. Buy Airtime                  │
│     6. Buy Data                     │
│     7. ATM Cash Out                 │
│     8. My Account                   │
│     0. Exit                         │
└─────────────────────────────────────┘
```

| Input | Destination |
|-------|-------------|
| 1 | Send Money → EnterPhoneState |
| 2 | Cash Out → EnterAmountState |
| 3 | Pay Bill → SelectBillerState |
| 4 | Pay Merchant → EnterCodeState |
| 5 | Buy Airtime → SelectTypeState |
| 6 | Buy Data → SelectNetworkState |
| 7 | ATM Cash Out → EnterAmountState |
| 8 | My Account → AccountMenuState |
| 0 | `END Thank you for using UmoPay. Goodbye!` |
| other | `END Invalid input. Please dial again.` |

---

### 6.3 1 · Send Money

**Four channels: UmoPay User · UmoPay Merchant · Mobile Money · Bank Account**

```
Screen 1 — Channel
┌─────────────────────────────────────┐
│ CON Send Money                      │
│     1. UmoPay User                  │
│     2. UmoPay Merchant              │
│     3. Mobile Money                 │
│     4. Bank Account                 │
│     0. Back                         │
└─────────────────────────────────────┘

── PATH A: UmoPay User (internal wallet-to-wallet) ──────────

Screen 2A — Enter phone (send_channel = "umopay_user", send_network = "UmoPay")
┌─────────────────────────────────────┐
│ CON Send Money (UmoPay)             │
│     Enter recipient phone number:   │
│     0. Back                         │
└─────────────────────────────────────┘
  [phone]  stores → recipient_phone, recipient_name (mock: "UmoPay User")
        ▼
Screen 3A — Enter Amount
┌─────────────────────────────────────┐
│ CON Send Money                      │
│     To: 0244999888 (UmoPay)         │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]
        ▼
Screen 4A — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Transfer                │
│     To: UmoPay User (0244999888)    │
│     Network: UmoPay                 │
│     Amount: GHS 50.00               │
│     Fee: GHS 0.00                   │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]  → END Transfer Successful! Sent GHS 50.00 to 0244999888 (UmoPay)

── PATH B: UmoPay Merchant ──────────────────────────────────

Screen 2B — Enter Merchant Code (send_channel = "umopay_merchant")
┌─────────────────────────────────────┐
│ CON Send to UmoPay Merchant         │
│     Enter merchant code:            │
│     0. Back                         │
└─────────────────────────────────────┘
  [code e.g. M00123]  stores → recipient_phone = code, recipient_name (mock: "UmoPay Merchant")
        ▼
Screen 3B — Enter Amount
┌─────────────────────────────────────┐
│ CON Send Money                      │
│     To: M00123 (UmoPay Merchant)    │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]
        ▼
Screen 4B — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Transfer                │
│     To: UmoPay Merchant (M00123)    │
│     Network: UmoPay Merchant        │
│     Amount: GHS 200.00              │
│     Fee: GHS 0.00                   │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]  → END Transfer Successful! Sent GHS 200.00 to M00123 (UmoPay Merchant)

── PATH C: Mobile Money ─────────────────────────────────────

Screen 2A — Select Network
┌─────────────────────────────────────┐
│ CON Send Money                      │
│     Select Network:                 │
│     1. MTN MoMo                     │
│     2. Telecel Cash                 │
│     3. AirtelTigo Money             │
│     4. G-Money                      │
│     0. Back                         │
└─────────────────────────────────────┘
  [1–4]  stores → send_network, send_channel = "mobile_money"
        ▼
Screen 3A — Enter Phone
┌─────────────────────────────────────┐
│ CON Send Money (MTN MoMo)           │
│     Enter recipient phone number:   │
│     0. Back                         │
└─────────────────────────────────────┘
  [phone]  stores → recipient_phone, recipient_name (mock: "UmoPay User")
        ▼
Screen 4A — Enter Amount
┌─────────────────────────────────────┐
│ CON Send Money                      │
│     To: 0244999888 (MTN MoMo)       │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]  stores → amount
        ▼
Screen 5A — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Transfer                │
│     To: UmoPay User (0244999888)    │
│     Network: MTN MoMo               │
│     Amount: GHS 100.00              │
│     Fee: GHS 0.00                   │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]  →  PIN OK → END Transfer Successful!
              Sent GHS 100.00 to 0244999888 (MTN MoMo)
              Ref: A3F9B12C44 / Time: 25 Mar 2026 14:30
         →  PIN wrong → END Transfer Failed / Incorrect PIN.

── PATH B: Bank Account ─────────────────────────────────────

Screen 2B — Select Bank (Page 1)
┌─────────────────────────────────────┐
│ CON Send to Bank (1/2)              │
│     1. GCB Bank                     │
│     2. Ecobank Ghana                │
│     3. Fidelity Bank                │
│     4. Stanbic Bank                 │
│     5. Absa Bank                    │
│     6. More banks...                │
│     0. Back                         │
└─────────────────────────────────────┘

  [6] — Page 2
┌─────────────────────────────────────┐
│ CON Send to Bank (2/2)              │
│     1. Access Bank                  │
│     2. CAL Bank                     │
│     3. Republic Bank                │
│     4. UBA Ghana                    │
│     5. Zenith Bank                  │
│     0. Back                         │
└─────────────────────────────────────┘
  [1–5]  stores → send_bank, send_channel = "bank"
        ▼
Screen 3B — Enter Account Number
┌─────────────────────────────────────┐
│ CON Send to GCB Bank                │
│     Enter account number:           │
│     0. Back                         │
└─────────────────────────────────────┘
  [account number]  stores → bank_account, recipient_name (mock: "Account Holder")
        ▼
Screen 4B — Enter Amount
┌─────────────────────────────────────┐
│ CON Send Money                      │
│     To: 1234567890 (GCB Bank)       │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]  stores → amount
        ▼
Screen 5B — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Transfer                │
│     To: Account Holder (1234567890) │
│     Bank: GCB Bank                  │
│     Amount: GHS 500.00              │
│     Fee: GHS 0.00                   │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]  →  PIN OK → END Transfer Successful!
              Sent GHS 500.00 to 1234567890 (GCB Bank)
              Ref: B7D3E19A02 / Time: 25 Mar 2026 14:31
         →  PIN wrong → END Transfer Failed / Incorrect PIN.
```

**Back-out at any screen:** press `0` — returns one level up toward the channel selection.

---

### 5.4 2 · Cash Out

```
Screen 1
┌─────────────────────────────────────┐
│ CON Cash Out                        │
│     Enter amount to withdraw (GHS): │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric amount]
        ▼
Screen 2 — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Cash Out                │
│     Amount: GHS 200.00              │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]
        │
    ┌───┴────┐
  PIN OK   PIN wrong
    ▼         ▼
END Cash Out  END Cash Out Failed
    Successful!   Incorrect PIN.
    Withdrew GHS 200.00
    Ref: 7D2E4A9F01
    Time: 25 Mar 2026 14:35
```

> Cash Out represents withdrawing from a mobile-money agent or partner point. The customer shows the agent the success screen or transaction reference; the agent pays out cash.

---

### 5.5 3 · Pay Bill

**4 billers supported: ECG · Ghana Water · DStv · GOtv**

```
Screen 1 — Select Biller
┌─────────────────────────────────────┐
│ CON Pay Bill                        │
│     1. ECG (Electricity)            │
│     2. Ghana Water Co.              │
│     3. DStv                         │
│     4. GOtv                         │
│     0. Back                         │
└─────────────────────────────────────┘
  [1–4]  stores → biller_code, biller_name
        ▼
Screen 2 — Enter Account/Meter No.
┌─────────────────────────────────────┐
│ CON Pay ECG (Electricity)           │
│     Enter account/meter number:     │
│     0. Back                         │
└─────────────────────────────────────┘
  [any text]  stores → account_number
        ▼
Screen 3 — Enter Amount
┌─────────────────────────────────────┐
│ CON Pay ECG (Electricity)           │
│     Account: 1234567890             │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]  stores → amount
        ▼
Screen 4 — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Bill Payment            │
│     Biller: ECG (Electricity)       │
│     Account: 1234567890             │
│     Amount: GHS 50.00               │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]
        │
    ┌───┴────┐
  PIN OK   PIN wrong
    ▼         ▼
END Bill Payment   END Bill Payment Failed
    Successful!        Incorrect PIN.
    Paid GHS 50.00
    to ECG (Electricity)
    Ref: C8A1D30E77
```

---

### 5.6 4 · Pay Merchant

```
Screen 1
┌─────────────────────────────────────┐
│ CON Pay Merchant                    │
│     Enter merchant code:            │
│     0. Back                         │
└─────────────────────────────────────┘
  [code]  stores → merchant_code, merchant_name (mock: "UmoPay Merchant")
        ▼
Screen 2
┌─────────────────────────────────────┐
│ CON Pay UmoPay Merchant             │
│     Code: M00123                    │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]  stores → amount
        ▼
Screen 3 — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Merchant Payment        │
│     Merchant: UmoPay Merchant       │
│     Code: M00123                    │
│     Amount: GHS 75.00               │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]
        │
    ┌───┴────┐
  PIN OK   PIN wrong
    ▼         ▼
END Payment       END Merchant Payment
    Successful!       Failed / Incorrect PIN.
    Paid GHS 75.00
    to UmoPay Merchant
    Ref: E5F2087B3C
```

---

### 5.7 5 · Buy Airtime

**"For Myself" skips phone entry but both paths converge at network selection (MTN / Telecel / AirtelTigo / Glo).**

```
Screen 1 — Type Selection
┌─────────────────────────────────────┐
│ CON Buy Airtime                     │
│     1. For Myself                   │
│     2. For Another Number           │
│     0. Back                         │
└─────────────────────────────────────┘

  [1 — Myself]                   [2 — Other]
  auto-sets airtime_phone              ▼
  to caller's own number    Screen 1B — Enter Phone
        │                   ┌──────────────────────────┐
        │                   │ CON Buy Airtime           │
        │                   │     Enter recipient       │
        │                   │     phone number:         │
        │                   │     0. Back               │
        │                   └──────────────────────────┘
        │                   [phone]  stores → airtime_phone
        │                              │
        └──────────────┬───────────────┘
                       ▼
Screen 2 — Select Network
┌─────────────────────────────────────┐
│ CON Buy Airtime                     │
│     For: 0244123456                 │
│     Select Network:                 │
│     1. MTN                          │
│     2. Telecel                      │
│     3. AirtelTigo                   │
│     4. Glo                          │
│     0. Back                         │
└─────────────────────────────────────┘
  [1–4]  stores → airtime_network
        ▼
Screen 3 — Enter Amount
┌─────────────────────────────────────┐
│ CON Buy Airtime                     │
│     For: 0244123456 (MTN)           │
│     Enter amount (GHS):             │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]  stores → amount
        ▼
Screen 4 — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Airtime Purchase        │
│     To: 0244123456                  │
│     Network: MTN                    │
│     Amount: GHS 10.00               │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]
        │
    ┌───┴────┐
  PIN OK   PIN wrong
    ▼         ▼
END Airtime Purchase   END Airtime Purchase
    Successful!            Failed / Incorrect PIN.
    GHS 10.00 sent to
    0244123456 (MTN)
    Ref: 2B9C47A015
```

---

### 5.8 6 · Buy Data

**"For Myself" skips phone entry — mirrors the Airtime flow. 4 networks × 3 bundle sizes = 12 combinations.**

```
Screen 1 — Type Selection
┌─────────────────────────────────────┐
│ CON Buy Data                        │
│     1. For Myself                   │
│     2. For Another Number           │
│     0. Back                         │
└─────────────────────────────────────┘

  [1 — Myself]                   [2 — Other]
  auto-sets data_phone                 ▼
  to caller's own number    Screen 1B — Enter Phone
        │                   ┌──────────────────────────┐
        │                   │ CON Buy Data              │
        │                   │     Enter phone number:   │
        │                   │     0. Back               │
        │                   └──────────────────────────┘
        │                   [phone]  stores → data_phone
        │                              │
        └──────────────┬───────────────┘
                       ▼
Screen 2 — Select Network
┌─────────────────────────────────────┐
│ CON Buy Data                        │
│     For: 0244123456                 │
│     Select Network:                 │
│     1. MTN                          │
│     2. Telecel                      │
│     3. AirtelTigo                   │
│     4. Glo                          │
│     0. Back                         │
└─────────────────────────────────────┘
  [1–4]  stores → data_network
        ▼
Screen 3 — Select Bundle
┌─────────────────────────────────────┐
│ CON Buy Data (MTN)                  │
│     For: 0244123456                 │
│     Select Bundle:                  │
│     1. 1GB  - GHS 5.00              │
│     2. 2GB  - GHS 9.00              │
│     3. 5GB  - GHS 20.00             │
│     0. Back                         │
└─────────────────────────────────────┘
  [1–3]  stores → bundle_label, amount
        ▼
Screen 4 — Confirm
┌─────────────────────────────────────┐
│ CON Confirm Data Purchase           │
│     Network: MTN                    │
│     To: 0244123456                  │
│     Bundle: 1GB                     │
│     Amount: GHS 5.00                │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]
        │
    ┌───┴────┐
  PIN OK   PIN wrong
    ▼         ▼
END Data Purchase     END Data Purchase
    Successful!           Failed / Incorrect PIN.
    1GB (MTN) sent
    to 0244123456
    Ref: F1A3290D88
```

---

### 5.9 7 · ATM Cash Out

Generates a **one-time 6-digit code** valid for 10 minutes. Customer uses this at a partner ATM — no debit card needed.

```
Screen 1
┌─────────────────────────────────────┐
│ CON ATM Cash Out                    │
│     Enter withdrawal amount (GHS):  │
│     A 6-digit code will be          │
│     generated.                      │
│     0. Back                         │
└─────────────────────────────────────┘
  [numeric]  stores → amount
        ▼
Screen 2 — Confirm
┌─────────────────────────────────────┐
│ CON ATM Cash Out                    │
│     Amount: GHS 300.00              │
│     A one-time code will be sent.   │
│     Use at any partner ATM.         │
│     --                              │
│     Enter PIN to confirm:           │
│     0. Cancel                       │
└─────────────────────────────────────┘
  [PIN]
        │
    ┌───┴────┐
  PIN OK   PIN wrong
    │         ▼
    │       END ATM Cash Out Failed
    │           Incorrect PIN.
    │
    ▼  AtmCashOutAction generates:
       • 6-digit zero-padded random code
       • expiry = now + 10 minutes (HH:mm)
    │
    ▼
END ATM Cash Out Code:
    *** 847291 ***
    Amount: GHS 300.00
    Expires: 15:45
    Visit any partner ATM
    to withdraw.
```

> **Important:** The code is displayed once and the session ends immediately. The customer must note it before the screen closes.

---

### 5.10 8 · My Account

```
Screen 1 — Account Sub-menu
┌─────────────────────────────────────┐
│ CON My Account                      │
│     1. Check Balance                │
│     2. Mini Statement               │
│     0. Back                         │
└─────────────────────────────────────┘

  [1 — Balance]              [2 — Mini Statement]
        ▼                          ▼
END Account Balance         END Mini Statement
    Phone: 0244123456            1. +GHS 200.00 Cash In
    Available: GHS 500.00           24 Mar 2026 09:10
    Ledger:    GHS 500.00        2. -GHS 50.00 Transfer
    As at: 25 Mar 2026 14:40        23 Mar 2026 14:32
                                 3. -GHS 30.00 Airtime
                                    22 Mar 2026 11:05

  [0 — Back]
        ▼
    Main Menu
```

---

## 6. Error & Dead-End States

All error states are `#[Terminate]` — the session ends immediately and the customer must re-dial.

| State | Trigger | Message |
|---|---|---|
| `InvalidPinState` | Wrong PIN at login entry | `Incorrect PIN. Please dial again to retry.` |
| `InvalidInputState` | Unrecognised menu selection | `Invalid input. Please dial again.` |
| `ExitState` | Press `0` at Main Menu | `Thank you for using UmoPay. Goodbye!` |
| `SendMoney\FailedState` | Wrong confirm PIN | `Transfer Failed — Incorrect PIN.` |
| `CashOut\FailedState` | Wrong confirm PIN | `Cash Out Failed — Incorrect PIN.` |
| `PayBill\FailedState` | Wrong confirm PIN | `Bill Payment Failed — Incorrect PIN.` |
| `PayMerchant\FailedState` | Wrong confirm PIN | `Merchant Payment Failed — Incorrect PIN.` |
| `Airtime\FailedState` | Wrong confirm PIN | `Airtime Purchase Failed — Incorrect PIN.` |
| `Data\FailedState` | Wrong confirm PIN | `Data Purchase Failed — Incorrect PIN.` |
| `AtmCashOut\FailedState` | Wrong confirm PIN | `ATM Cash Out Failed — Incorrect PIN.` |

---

## 7. Session Data Reference

The `Record` object persists these keys across screens within a single USSD session:

| Key | Set by | Used by |
|---|---|---|
| `phone_number` | `UssdController` (`Context::with`) | `MainMenuState`, `BalanceState` |
| `pin` | `EnterPinState::setPin` | `AuthAction` |
| `confirm_pin` | Every `ConfirmState::setPin` | Every Action class |
| `send_channel` | `SelectMobileNetworkState` / `SelectBankState` / `SelectBankState2` | `ConfirmState`, `SuccessState` |
| `send_network` | `SelectMobileNetworkState` | `EnterPhoneState`, `EnterAmountState`, `ConfirmState`, `SuccessState` |
| `send_bank` | `SelectBankState` / `SelectBankState2` | `EnterBankAccountState`, `EnterAmountState`, `ConfirmState`, `SuccessState` |
| `bank_account` | `EnterBankAccountState` | — (display via `recipient_phone`) |
| `recipient_phone` | `SendMoney\EnterPhoneState` / `EnterBankAccountState` | `EnterAmountState`, `ConfirmState`, `SuccessState` |
| `recipient_name` | `SendMoney\EnterPhoneState` / `EnterBankAccountState` (mock) | `ConfirmState` |
| `amount` | `EnterAmountState` / `SelectBundleState` | `ConfirmState`, `SuccessState` |
| `biller_code` | `SelectBillerState` | — |
| `biller_name` | `SelectBillerState` | `EnterAccountState`, `ConfirmState`, `SuccessState` |
| `account_number` | `PayBill\EnterAccountState` | `EnterAmountState`, `ConfirmState` |
| `merchant_code` | `EnterCodeState` | `EnterAmountState`, `ConfirmState` |
| `merchant_name` | `EnterCodeState` (mock) | `EnterAmountState`, `ConfirmState`, `SuccessState` |
| `airtime_phone` | `SelectTypeState` (self) / `Airtime\EnterPhoneState` | `SelectNetworkState`, `EnterAmountState`, `ConfirmState`, `SuccessState` |
| `airtime_network` | `Airtime\SelectNetworkState` | `EnterAmountState`, `ConfirmState`, `SuccessState` |
| `data_network` | `SelectNetworkState` | `EnterPhoneState`, `SelectBundleState`, `ConfirmState`, `SuccessState` |
| `data_phone` | `Data\EnterPhoneState` | `SelectBundleState`, `ConfirmState`, `SuccessState` |
| `bundle_label` | `SelectBundleState` | `ConfirmState`, `SuccessState` |
| `atm_code` | `AtmCashOutAction` | `ShowCodeState` |
| `atm_expiry` | `AtmCashOutAction` | `ShowCodeState` |
| `txn_ref` | All Action classes (mock MD5) | All `SuccessState` classes |
| `txn_time` | All Action classes (mock) | `SendMoney`, `CashOut`, `Airtime`, `Data` SuccessStates |
| `error` | All Action classes on failure | All `FailedState` classes |

---

## 8. Current Mock Values (Dev)

> These must be replaced when connecting to a real backend API.

| Location | Mock value | Replace with |
|---|---|---|
| `AuthAction` | PIN `1234` | `POST /api/auth/verify-pin` |
| All Action classes | PIN `1234` for confirm | Same auth endpoint or JWT session check |
| `SendMoney\EnterPhoneState` | `recipient_name = 'UmoPay User'` | `GET /api/users/lookup?phone=X` |
| `SendMoney\EnterBankAccountState` | `recipient_name = 'Account Holder'` | GhIPSS name enquiry — `GET /api/banks/name-enquiry?bank=X&account=Y` |
| `PayMerchant\EnterCodeState` | `merchant_name = 'UmoPay Merchant'` | `GET /api/merchants/{code}` |
| `BalanceState` | Available & Ledger GHS 500.00 | `GET /api/wallets/balance` |
| `MiniStatementState` | 3 hardcoded transactions | `GET /api/transactions?limit=5` |
| All SuccessStates | `txn_ref` = random 10-char MD5 | Real reference from API response |
| `AtmCashOutAction` | Random 6-digit `random_int` code | Backend-issued OTP stored with wallet ID + expiry |

---

## 9. Gaps & Recommended Improvements

### 9.1 Missing: Self-Service Registration

There is no USSD onboarding path. A new user who has never registered hits `InvalidPinState` with no guidance.

**Proposed first-time flow:**

```
Dial *XXX#
    ▼
CON Welcome to UmoPay
    1. I have an account
    2. Register
    0. Exit

[2 — Register]
    ▼
Enter your full name:
    ▼
Enter your Ghana Card number:
    ▼
Create a 4-digit PIN:
    ▼
Confirm PIN:
    ▼
END Registration Successful!
    Your wallet is ready.
    Dial *XXX# to get started.
```

---

### 9.2 Missing: PIN Reset / Forgot PIN

A customer who forgets their PIN has no recovery path from USSD. They cannot unlock their account without calling support.

**Recommended:** Add `3. Forgot PIN` on the entry screen → send OTP to registered number → verify OTP → set new PIN in-session.

---

### 9.3 Security: Session Authentication Token

Currently `confirm_pin` is validated against the same hardcoded `MOCK_PIN = '1234'` used for login. In production:
- Validate the confirm PIN server-side and tie it to an authenticated session token.
- Consider storing a short-lived JWT in `Record` after `AuthAction` succeeds.
- Lock the account after 3 consecutive wrong confirm-PINs within a session before terminating.

---

### 9.4 UX: Failed Transactions Force Re-dial

All failure states are `#[Terminate]`, forcing the customer to re-dial and re-authenticate from scratch. For PIN-entry failures, consider a **retry path** — go back to the confirmation screen and allow one more attempt before terminating.

---

### ~~9.5 UX: Buy Data Had No "For Myself" Shortcut~~ ✓ Resolved

Buy Data now has a `SelectTypeState` matching the Airtime pattern. Both services use the same flow: type selection → (phone entry if Another) → network → bundle/amount → confirm.

---

### 9.6 UX: Confirmation Screen Should Show Available Balance

Before PIN confirmation, display the customer's wallet balance alongside the transaction amount. This prevents declined transactions from insufficient funds.

```
Confirm Bill Payment
Biller: ECG (Electricity)
Account: 1234567890
Amount: GHS 50.00
Balance: GHS 500.00     ← add this line
--
Enter PIN to confirm:
0. Cancel
```

---

### 9.7 Missing: Transaction Receipt via SMS

After a successful transaction the END screen is the customer's only record — and it disappears as soon as they press OK or the session closes. Integrate Africa's Talking SMS API in each `SuccessState` to push a receipt SMS to `phone_number`.

---

### 9.8 Missing: Insufficient Funds Check

Every action validates only the PIN. In production, actions must check wallet balance before processing and return a clear error if funds are insufficient — rather than letting the backend fail and returning a generic "Transaction failed."

```php
if ($balance < $amount) {
    $record->set('error', 'Insufficient funds. Balance: GHS ' . $balance);
    return FailedState::class;
}
```

---

### 9.9 Missing: ATM Code Backend Persistence

`AtmCashOutAction` generates the 6-digit code in memory and stores it only in the USSD session `Record`. If the session cache expires before the customer reaches the ATM, the code is lost and unverifiable. The code must be **persisted to the backend database** with the wallet ID, reserved amount, and expiry so the ATM terminal can validate it independently.

---

### 9.10 Missing: Observability / Audit Trail

There are no logs tied to USSD sessions. At a minimum, each action should emit a structured log entry containing: `sessionId`, `phoneNumber`, action name, amount, `txnRef`, and result. This is essential for dispute resolution and fraud detection.

---

### 9.11 Input Validation Hardening

| Field | Current validation | Recommended addition |
|---|---|---|
| Phone numbers (Send Money, Airtime) | Any string accepted | Regex `^0[235][0-9]{8}$` (Ghana mobile) |
| Amounts | `IsNumeric` only | Minimum GHS 1; per-service maximum (e.g. GHS 5 000 for Send Money) |
| Bill account/meter numbers | Any string accepted | Regex per biller (ECG meter = 13 digits, etc.) |
| Merchant code | Any string accepted | Lookup + existence check against merchant registry |
| ATM withdrawal amount | `IsNumeric` only | Must be a multiple of 50 (standard ATM denomination) |

---

### 9.12 Internationalisation

All screens are English-only. Consider a language selection screen at session start and storing `lang` in the `Record` for multilingual menu rendering.

```
Welcome to UmoPay
1. English
2. Twi
3. Hausa
4. Ewe
```

---

*README written 25 Mar 2026 — reflects codebase at current state with mock/stub implementations.*
