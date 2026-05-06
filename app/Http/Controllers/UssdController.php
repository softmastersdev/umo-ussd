<?php

namespace App\Http\Controllers;

use App\Ussd\Configurators\AfricasTalking;
use App\Ussd\Configurators\Arkesel;
use App\Ussd\Configurators\Nalo;
use App\Ussd\States\EnterPinState;
use Sparors\Ussd\Ussd;

class UssdController extends Controller
{
    /**
     * Shared runner — apply a configurator then start the state machine.
     */
    private function run(string $configurator): mixed
    {
        return Ussd::build()
            ->useConfigurator($configurator)
            ->useInitialState(EnterPinState::class)
            ->run();
    }

    /**
     * Africa's Talking callback.
     * POST /ussd/at  (also kept at POST /ussd for backward compatibility)
     */
    public function africasTalking(): mixed
    {
        return $this->run(AfricasTalking::class);
    }

    /**
     * Nalo Solutions callback.
     * POST /ussd/nalo
     */
    public function nalo(): mixed
    {
        return $this->run(Nalo::class);
    }

    /**
     * Arkesel callback.
     * POST /ussd/arkesel
     */
    public function arkesel(): mixed
    {
        return $this->run(Arkesel::class);
    }
}
