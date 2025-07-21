<div class="js-cookie-consent cookie-consent fixed bottom-0 inset-x-0 pb-2 z-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="p-4 md:p-6 rounded-lg bg-white shadow-lg border border-gray-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1 items-center">
                    <p class="text-gray-800 cookie-consent__message leading-relaxed">
                        {!! trans('cookie-consent::texts.message') !!}
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 ml-6">
                    <button class="js-cookie-consent-agree cookie-consent__agree cursor-pointer flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white whitespace-nowrap transition-colors duration-200" style="background-color: #887474;" onmouseover="this.style.backgroundColor='#776565'" onmouseout="this.style.backgroundColor='#887474'">
                        {{ trans('cookie-consent::texts.agree') }}
                    </button>
                    <div class="flex gap-2">
                        <a href="/privacy-policy" target="_blank" class="flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 border border-gray-300 whitespace-nowrap">
                            Privacy Policy
                        </a>
                        <a href="/terms-of-use" target="_blank" class="flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 border border-gray-300 whitespace-nowrap">
                            Terms of Use
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
