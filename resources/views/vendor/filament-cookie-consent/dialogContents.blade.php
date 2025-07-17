<div class="js-cookie-consent cookie-consent {{ $pluginConfig['position'] === 'start' ? 'sticky left-0 top-0' : 'fixed bottom-0 left-0' }} z-50 w-full p-4 bg-white border-t border-gray-200 shadow md:p-6 dark:bg-gray-800 dark:border-gray-600">
    <div class="flex justify-center flex-row">
        <div class="basis-3/4 max-w-7xl">
            {!! trans('cookie-consent::texts.message') !!}
        </div>
        <div class="basis-1/4 mx-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-2">
                <x-filament::button
                    size="{{ $pluginConfig['consent_button']['size'] ?? 'sm' }}"
                    color="{{ $pluginConfig['consent_button']['color'] ?? 'warning' }}"
                    class="js-cookie-consent-agree cookie-consent__agree"
                >
                    {{ trans('cookie-consent::texts.agree') }}
                </x-filament::button>
                
                <div class="flex flex-col gap-1 sm:flex-row sm:gap-2">
                    @if($pluginConfig['privacy_policy_button']['enabled'])
                        <x-filament::button
                            size="{{ $pluginConfig['privacy_policy_button']['size'] ?? 'sm' }}"
                            href="{{ url($pluginConfig['privacy_policy_button']['href']) ?? '/privacy-policy' }}"
                            target="{{ $pluginConfig['privacy_policy_button']['target'] ?? '_blank' }}"
                            tag="a"
                            color="{{ $pluginConfig['privacy_policy_button']['color'] ?? 'gray' }}"
                            outlined
                        >
                            Privacy Policy
                        </x-filament::button>
                    @endif
                    
                    @if($pluginConfig['terms_of_use_button']['enabled'])
                        <x-filament::button
                            size="{{ $pluginConfig['terms_of_use_button']['size'] ?? 'sm' }}"
                            href="{{ url($pluginConfig['terms_of_use_button']['href']) ?? '/terms-of-use' }}"
                            target="{{ $pluginConfig['terms_of_use_button']['target'] ?? '_blank' }}"
                            tag="a"
                            color="{{ $pluginConfig['terms_of_use_button']['color'] ?? 'gray' }}"
                            outlined
                        >
                            Terms of Use
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
