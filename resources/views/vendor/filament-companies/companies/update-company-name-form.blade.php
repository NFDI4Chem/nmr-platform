<x-filament-companies::grid-section md="1">
    <x-slot name="title">
        {{ __('filament-companies::default.grid_section_titles.company_name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('filament-companies::default.grid_section_descriptions.company_name') }}
    </x-slot>

    <x-filament::section>
        <x-filament-panels::form wire:submit="updateCompanyName">
                <!-- Company Owner Information -->
                <x-filament-forms::field-wrapper.label>
                    {{ __('filament-companies::default.labels.company_owner') }}
                </x-filament-forms::field-wrapper.label>

                <div class="flex items-center text-sm">
                    <div class="flex-shrink-0">
                        <x-filament-panels::avatar.user :user="$company->owner" style="height: 3rem; width: 3rem;" />
                    </div>
                    <div class="ml-4">
                        <div class="font-medium text-gray-900 dark:text-gray-200">{{ $company->owner->name }}</div>
                        <div class="text-gray-600 dark:text-gray-400">{{ $company->owner->email }}</div>
                    </div>
                </div>
        </x-filament-panels::form>
    </x-filament::section>
</x-filament-companies::grid-section>
