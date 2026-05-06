<?php

namespace App\Ussd\States\Data;

use App\Ussd\States\InvalidInputState;
use App\Ussd\States\MainMenuState;
use Sparors\Ussd\Attributes\Transition;
use Sparors\Ussd\Context;
use Sparors\Ussd\Contracts\State;
use Sparors\Ussd\Decisions\Equal;
use Sparors\Ussd\Decisions\Fallback;
use Sparors\Ussd\Decisions\In;
use Sparors\Ussd\Menu;
use Sparors\Ussd\Record;

#[Transition(to: MainMenuState::class, match: new Equal('0'))]
#[Transition(to: ConfirmState::class,  match: new In('1', '2', '3'), callback: [self::class, 'setBundle'])]
#[Transition(to: InvalidInputState::class, match: new Fallback())]
class SelectBundleState implements State
{
    private array $bundles = [
        '1' => ['label' => '1GB',  'price' => 5.00],
        '2' => ['label' => '2GB',  'price' => 9.00],
        '3' => ['label' => '5GB',  'price' => 20.00],
    ];

    public function render(Record $record): Menu
    {
        $network = $record->get('data_network', '');
        $phone   = $record->get('data_phone', '');

        return Menu::build()
            ->line("Buy Data ($network)")
            ->line("For: $phone")
            ->line('Select Bundle:')
            ->line('1. 1GB  - GHS 5.00')
            ->line('2. 2GB  - GHS 9.00')
            ->line('3. 5GB  - GHS 20.00')
            ->text('0. Back');
    }

    public function setBundle(Context $context, Record $record): void
    {
        $key    = $context->input();
        $bundle = $this->bundles[$key] ?? ['label' => '1GB', 'price' => 5.00];

        $record->set('bundle_label', $bundle['label']);
        $record->set('amount', (string) $bundle['price']);
    }
}
