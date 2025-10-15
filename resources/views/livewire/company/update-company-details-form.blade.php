<x-filament::section>
    <x-filament-panels::form wire:submit="updateCompanyProfile">
        {{ $this->form }}
        <div class="text-left">
            <x-filament::button type="submit">
                {{ __('filament-companies::default.buttons.save') }}
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament::section>

