<x-guest-layout>
    <div class="bg-white">
        <main>
            <div class="overflow-hidden hpt-8 sm:pt-12 lg:relative lg:py-10">
                <div class="align-middle h-screen flex items-center mx-auto max-w-md px-6 sm:max-w-3xl lg:grid lg:max-w-7xl lg:grid-cols-2 lg:gap-24 lg:px-8">
                    <div>
                        <x-application-logo class="h-12 w-auto" />
                        <div class="mt-10">
             
                            <div class="mt-6 sm:max-w-xl">
                                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">Ensuring seamless research data management (RDM) of your sample's NMR data</h1>
                                <!-- <p class="mt-6 text-xl text-gray-500">Supports seamlessly smooth submission of your NMR samples.</p> -->
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <div class="mt-10 flex items-center gap-x-6">
                                    <a href="/login"
                                        class="rounded-md bg-rose-600 px-3.5 py-2.5 font-semibold text-white shadow-sm hover:bg-rose-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"> Submit Samples
                                    </a>
                                    <a href="/register"
                                        class="rounded-md bg-white-600 px-3.5 py-2.5 font-semibold text-rose-600 shadow-sm border border-rose-100 hover:bg-rose-500 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">Register
                                    </a>
                                    <!-- <a target="_blank" href="https://nmrxivplatform.in/features.html"
                                        class="text-sm font-semibold leading-6 text-gray-900">Learn more
                                        <span aria-hidden="true">→</span></a> -->
                                </div>
                        </div>
                    </div>
                </div>
                <div class="sm:mx-auto sm:max-w-3xl sm:px-6">
                    <div class="py-12 sm:relative sm:mt-12 sm:py-16 lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
                        <div class="hidden sm:block">
                            <div
                                class="absolute inset-y-0 left-1/2 w-screen rounded-l-3xl bg-gray-50 lg:left-80 lg:right-0 lg:w-full">
                            </div>
                        </div>
                        <div
                            class="relative -mr-40 pl-6 sm:mx-auto sm:max-w-3xl sm:px-0 lg:h-full lg:max-w-none lg:pl-12">
                            <img class="w-full rounded-md ring-opacity-5 lg:h-full lg:w-auto lg:max-w-none"
                                src="/img/header_img.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        @include('components.tawk-chat')
        </main>
    </div>
</x-guest-layout>
