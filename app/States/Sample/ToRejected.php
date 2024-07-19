<?php

namespace App\States\Sample;

use App\Models\Sample;
use Filament\Forms\Components\Textarea;
use Maartenpaauw\Filament\ModelStates\Concerns\ProvidesSpatieTransitionToFilament;
use Maartenpaauw\Filament\ModelStates\Contracts\FilamentSpatieTransition;
use Spatie\ModelStates\Transition;

/**
 * @implements FilamentSpatieTransition<Order>
 */
final class ToRejected extends Transition implements FilamentSpatieTransition
{
    use ProvidesSpatieTransitionToFilament;

    public function __construct(
        private readonly Sample $sample,
        private readonly string $reason = '',
    ) {}

    public function handle(): Sample
    {
        $this->sample->state = new FinishedState($this->sample);
        $this->sample->comments = $this->reason;

        $this->sample->save();

        return $this->sample;
    }

    public function form(): array
    {
        return [
            Textarea::make('comments')
                ->required()
                ->minLength(1)
                ->maxLength(1000)
                ->rows(5)
                ->helperText(__('This reason will be sent to the customer.')),
        ];
    }
}
