<?php

namespace App\States\Sample;

use Filament\Facades\Filament;
use Maartenpaauw\Filament\ModelStates\Concerns\ProvidesSpatieStateToFilament;
use Maartenpaauw\Filament\ModelStates\Contracts\FilamentSpatieState;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * @extends State<\App\Models\Payment>
 */
abstract class SampleState extends State implements FilamentSpatieState
{
    use ProvidesSpatieStateToFilament;
    // abstract public function color(): string;

    public static function config(): StateConfig
    {
        if (Filament::getTenant()) {
            return parent::config()
                ->default(DraftState::class)
                ->allowTransition(DraftState::class, SubmittedState::class);
        } else {
            return parent::config()
                ->default(DraftState::class)
                ->allowTransition(DraftState::class, SubmittedState::class)
                ->allowTransition(SubmittedState::class, ReceivedState::class)
                // ->allowTransition([SubmittedState::class, ReceivedState::class], RejectedState::class, ToRejected::class)
                // ->allowTransition(SubmittedState::class, RejectedState::class, ToRejected::class)
                // ->allowTransition(RejectedState::class, SubmittedState::class)
                ->allowTransition(ReceivedState::class, ProcessingState::class);
            // ->allowTransition(ProcessingState::class, FinishedState::class)
            // ->allowTransition(FinishedState::class, ProcessingState::class);
        }

    }
}
