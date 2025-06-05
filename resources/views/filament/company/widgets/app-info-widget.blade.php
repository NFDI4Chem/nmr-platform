<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex flex-col">
                <div class="flex items-center gap-x-2">
                    <span class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-500 dark:text-gray-400">
                        PROFILE
                    </span>
                </div>
                <div
                    class="fi-wi-stats-overview-stat-value text-4xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $this->getProfileCompletionPercentage() }}%
                </div>
                <div class="flex items-center gap-x-1">
                    <span
                        class="fi-wi-stats-overview-stat-description text-sm fi-color-custom text-custom-600 dark:text-custom-400">
                        complete
                    </span>
                </div>
            </div>
            <div class="flex-1 items-start gap-y-1 pl-2">
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700"
                    role="progressbar" aria-valuenow="{{ $this->getProfileCompletionPercentage() }}" aria-valuemin="0" aria-valuemax="100">
                    @php
                        $percentage = $this->getProfileCompletionPercentage();
                    @endphp
                    <div class="h-full rounded-full bg-gray-600 text-xs text-white text-center whitespace-nowrap transition duration-500 dark:bg-gray-500" style="width: {{ $percentage }}%"></div>
                </div>
                <h2 class="text-lg font-semibold text-left mb-1 mt-2">Complete your profile to unlock features!</h2>
                <!-- Description with link to more info -->
                <p class="text-xs text-left text-gray-600 mb-2">
                    Provide details like bank account info, tax registrations, and fiscal year details to unlock more
                    features and ensure financial compliance.
                </p>
                <div class="flex justify-between w-full">
                    <a href="{{ $this->getCompanyId() }}/profile" class="bg-gray-600 text-white py-1 px-3 rounded-full text-xs">Complete profile</a>
                    <a class="text-gray-600 underline text-sm self-center">Why is this important?</a>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
