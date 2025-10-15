<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Quick Actions
        </x-slot>

        <x-slot name="description">
            Common tasks and shortcuts
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($this->getActions() as $action)
                @php
                    $colorClasses = [
                        'primary' => [
                            'icon' => 'text-primary-500 group-hover:text-primary-600',
                            'border' => 'hover:border-primary-500',
                            'text' => 'group-hover:text-primary-600 dark:group-hover:text-primary-400',
                            'arrow' => 'group-hover:text-primary-500',
                        ],
                        'gray' => [
                            'icon' => 'text-gray-500 group-hover:text-gray-600',
                            'border' => 'hover:border-gray-500',
                            'text' => 'group-hover:text-gray-600 dark:group-hover:text-gray-400',
                            'arrow' => 'group-hover:text-gray-500',
                        ],
                    ];
                    $colors = $colorClasses[$action['color']] ?? $colorClasses['gray'];
                @endphp
                <a href="{{ $action['url'] }}" 
                   class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 {{ $colors['border'] }} hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200 group">
                    <div class="flex-shrink-0 mt-1">
                        <x-filament::icon 
                            :icon="$action['icon']"
                            class="h-8 w-8 {{ $colors['icon'] }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white {{ $colors['text'] }}">
                            {{ $action['label'] }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $action['description'] }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 mt-1">
                        <x-filament::icon 
                            icon="heroicon-m-arrow-right"
                            class="h-5 w-5 text-gray-400 {{ $colors['arrow'] }} group-hover:translate-x-1 transition-all duration-200" />
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

