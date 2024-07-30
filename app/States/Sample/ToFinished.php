<?php

namespace App\States\Sample;

use App\Models\Sample;
use Maartenpaauw\Filament\ModelStates\Concerns\ProvidesSpatieTransitionToFilament;
use Maartenpaauw\Filament\ModelStates\Contracts\FilamentSpatieTransition;
use Spatie\ModelStates\Transition;

/**
 * @implements FilamentSpatieTransition<Order>
 */
final class ToFinished extends Transition implements FilamentSpatieTransition
{
    use ProvidesSpatieTransitionToFilament;

    public function __construct(
        private readonly Sample $sample,
    ) {}

    public function handle(): Sample
    {
        $this->sample->state = new FinishedState($this->sample);
        $this->sample->cancelled_at = now();

        $this->sample->save();

        return $this->sample;
    }
}
