<?php

use App\Http\Controllers\UssdController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| USSD Gateway Endpoints
|--------------------------------------------------------------------------
| Each gateway provider posts to its own URL so the correct configurator
| is applied automatically. Add the relevant URL to your AT / Nalo /
| Arkesel dashboard under "Callback URL".
|
| You can run all three simultaneously — each session is isolated by the
| sessionId supplied by the gateway, stored in the file cache store.
*/

// Africa's Talking
Route::post('/ussd/at',      [UssdController::class, 'africasTalking']);

// Nalo Solutions
Route::post('/ussd/nalo',    [UssdController::class, 'nalo']);

// Arkesel
Route::post('/ussd/arkesel', [UssdController::class, 'arkesel']);

// Backward-compatible alias (originally pointed at AT)
Route::post('/ussd',         [UssdController::class, 'africasTalking']);
