<x-guest-layout>
    <div class="bg-white">
        <!-- Header -->
        <header x-data="{ open: false }" @keydown.window.escape="open = false" class="absolute inset-x-0 top-0 z-50">
            <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <a href="#" class="-m-1.5 p-1.5">
                        <span class="sr-only">NFDI4Chem NMR Platform</span>
                        <img class="h-12 w-auto" src="/img/logo.svg" alt="NMR Platform Logo">
                    </a>
                </div>
                <div class="flex lg:hidden">
                    <button type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700" @click="open = true">
                        <span class="sr-only">Open main menu</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
                        </svg>
                    </button>
                </div>
                <div class="hidden lg:flex lg:gap-x-12">
                </div>
                <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                @if (Route::has('login'))
                        <div class="flex items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-block px-5 py-1.5 text-sm/6 font-semibold text-gray-900 border border-black hover:border-gray-800 rounded-md">
                                    Dashboard
                                </a>
                            @else
                                <a href="/login" class="text-sm/6 font-semibold text-gray-900">Log in <span aria-hidden="true">→</span></a>
                            @endauth
                        </div>
                    @endif
                </div>
            </nav>
            <!-- Mobile menu -->
            <div x-description="Mobile menu, show/hide based on menu open state." class="lg:hidden" x-ref="dialog" x-show="open" aria-modal="true" style="display: none;">
                <div x-description="Background backdrop, show/hide based on slide-over state." class="fixed inset-0 z-50"></div>
                <div class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10" @click.away="open = false">
                    <div class="flex items-center justify-between">
                        <a href="#" class="-m-1.5 p-1.5">
                            <span class="sr-only">NFDI4Chem NMR Platform</span>
                            <img class="h-8 w-auto" src="/img/logo.svg" alt="NMR Platform Logo">
                        </a>
                        <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700" @click="open = false">
                            <span class="sr-only">Close menu</span>
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6 flow-root">
                        <div class="-my-6 divide-y divide-gray-500/10">
                            <div class="py-6">
                                <a href="/login" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Log in</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="isolate">
            <!-- Hero section -->
            <div class="relative pt-14">
                <div class="py-24 sm:py-32 lg:pb-40">
                    <div class="mx-auto max-w-7xl px-6 lg:px-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <h1 class="text-5xl font-semibold text-balance text-gray-900 sm:text-5xl">Manage, Share and Integrate Your NMR Research Data test</h1>
                            <p class="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">A comprehensive platform for NMR data management from sample submission to automated data processing & advanced visualization capabilities.</p>
                            <div class="mt-10 flex items-center justify-center gap-x-6">
                                <a href="/register" class="rounded-md bg-blue-700 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Get started</a>
                            </div>
                        </div>
                        <div class="mt-16 flow-root sm:mt-24">
                            <div class="-m-2 p-2 ring-1 ring-gray-900/10 ring-inset lg:-m-4 lg:rounded-2xl lg:p-4">
                                <iframe src="https://nmrium.nmrxiv.org?workspace=nmrxiv" style="width: 100%; height: 800px; border: none;"></iframe>
                            </div>
                        </div>
                        <div class="mt-8 flex">
                            <p class="w-full relative lg:rounded-xl px-6 py-3 text-sm/6 bg-amber-50 text-amber-800 ring-1 ring-amber-200 ring-inset">
                                <span class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="hidden md:inline">NMR data processing occurs locally in your browser. No data is transmitted to or stored on our servers.</span><a class="block font-semibold text-amber-900 hover:text-amber-950"><span class="absolute inset-0" aria-hidden="true"></span>Export your data before refreshing <span aria-hidden="true">→</span></a>
                                </span>

                            </p>
                        </div>
                    </div>
                </div>
                <div class="absolute inset-0 -z-10">
                    <img alt="" loading="lazy" height="946" decoding="async" data-nimg="1" class="w-full h-full object-cover opacity-50" style="color:transparent" src="https://salient.tailwindui.com/_next/static/media/background-faqs.55d2e36a.jpg">
                </div>
            </div>

            <!-- Feature section -->
            <div class="mx-auto max-w-7xl px-6 lg:px-8 mt-10">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base/7 font-semibold text-blue-700">Research Tools</h2>
                    <p class="mt-2 text-4xl font-semibold tracking-tight text-pretty text-gray-900 sm:text-5xl lg:text-balance">Everything you need for NMR research</p>
                    <p class="mt-6 text-lg/8 text-pretty text-gray-600">nmrXiv platform provides comprehensive tools for NMR data management, analysis, and collaboration, making your research more efficient and reproducible.</p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-gray-900">
                                <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-700">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z"></path>
                                    </svg>
                                </div>
                                Sample Submission
                            </dt>
                            <dd class="mt-2 text-base/7 text-gray-600">Easily submit and track your NMR samples through our streamlined submission process. Get real-time updates on your sample status.</dd>
                        </div>
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-gray-900">
                                <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-700">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"></path>
                                    </svg>
                                </div>
                                Data Security
                            </dt>
                            <dd class="mt-2 text-base/7 text-gray-600">Your research data is protected with enterprise-grade security measures. Control access and sharing permissions for your data.</dd>
                        </div>
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-gray-900">
                                <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-700">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                                    </svg>
                                </div>
                                Data Analysis
                            </dt>
                            <dd class="mt-2 text-base/7 text-gray-600">Advanced tools for processing and analyzing NMR spectra. Export data in multiple formats for further analysis.</dd>
                        </div>
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-gray-900">
                                <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-700">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 0 0 4.5 10.5a7.464 7.464 0 0 1-1.15 3.993m1.989 3.559A11.209 11.209 0 0 0 8.25 10.5a3.75 3.75 0 1 1 7.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 0 1-3.6 9.75m6.633-4.596a18.666 18.666 0 0 1-2.485 5.33"></path>
                                    </svg>
                                </div>
                                Collaboration
                            </dt>
                            <dd class="mt-2 text-base/7 text-gray-600">Share your research data with collaborators securely. Work together on data analysis and interpretation in real-time.</dd>
                        </div>
                    </dl>
                </div>
            </div>
            <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-56 lg:px-8">
                <!-- Logo cloud -->
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-12 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 sm:gap-y-14 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                        <div class="col-span-2 flex justify-center lg:col-span-1">
                            <a target="_blank" href="/about-us">
                                <img class="max-h-12 w-full object-contain" src="https://www.uni-jena.de/unijenamedia/universitaet/abteilung-hochschulkommunikation/marketing/wort-bildmarke-universitaet-jena.jpg?height=335&width=1000" alt="FSU Jena">
                            </a>
                        </div>
                        <div class="col-span-2 flex justify-center lg:col-span-1">
                            <a target="_blank" href="https://www.nfdi4chem.de/">
                                <img class="max-h-12 w-full object-contain" src="https://www.nmrxiv.org/img/nmrxiv-logo.png" alt="NFDI4Chem">
                            </a>
                        </div>
                        <div class="col-span-2 flex justify-center lg:col-span-1">
                            <a target="_blank" href="https://pharmacy.uic.edu/">
                                <img class="max-h-12 w-full object-contain" src="https://www.nmrxiv.org/img/nmrium-logo.png" alt="UIC">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- FAQs -->
            <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-56 lg:px-8">
                <div class="mx-auto max-w-2xl px-6 pb-8 sm:pt-12 sm:pb-24 lg:max-w-7xl lg:px-8 lg:pb-32">
                    <h2 class="text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">Frequently asked questions</h2>
                    <dl class="mt-20 divide-y divide-gray-900/10">
                        <div class="py-8 first:pt-0 last:pb-0 lg:grid lg:grid-cols-12 lg:gap-8">
                            <dt class="text-base/7 font-semibold text-gray-900 lg:col-span-5">What is nmrXiv Platform?</dt>
                            <dd class="mt-4 lg:col-span-7 lg:mt-0">
                                <p class="text-base/7 text-gray-600">nmrXiv is a comprehensive NMR data management platform that enables researchers to submit samples, process data, and share results. It follows FAIR data principles and provides advanced visualization tools for NMR spectroscopy data analysis.</p>
                            </dd>
                        </div>
                        <div class="py-8 first:pt-0 last:pb-0 lg:grid lg:grid-cols-12 lg:gap-8">
                            <dt class="text-base/7 font-semibold text-gray-900 lg:col-span-5">How do I submit my NMR samples?</dt>
                            <dd class="mt-4 lg:col-span-7 lg:mt-0">
                                <p class="text-base/7 text-gray-600">You can submit your NMR samples through our user-friendly interface. Simply create an account, fill out the sample submission form with your experimental details, and upload your data. Our system will automatically process and store your data securely.</p>
                            </dd>
                        </div>
                        <div class="py-8 first:pt-0 last:pb-0 lg:grid lg:grid-cols-12 lg:gap-8">
                            <dt class="text-base/7 font-semibold text-gray-900 lg:col-span-5">Is my research data secure?</dt>
                            <dd class="mt-4 lg:col-span-7 lg:mt-0">
                                <p class="text-base/7 text-gray-600">Yes, we take data security seriously. Your research data is encrypted, and you have full control over access permissions. You can choose to keep your data private or share it with specific collaborators or the wider scientific community.</p>
                            </dd>
                        </div>
                        <div class="py-8 first:pt-0 last:pb-0 lg:grid lg:grid-cols-12 lg:gap-8">
                            <dt class="text-base/7 font-semibold text-gray-900 lg:col-span-5">What file formats are supported?</dt>
                            <dd class="mt-4 lg:col-span-7 lg:mt-0">
                                <p class="text-base/7 text-gray-600">Our platform supports all major NMR file formats including Bruker, Varian/Agilent, and JEOL data formats. We also support common export formats for processed data, ensuring compatibility with other analysis tools.</p>
                            </dd>
                        </div>
                        <div class="py-8 first:pt-0 last:pb-0 lg:grid lg:grid-cols-12 lg:gap-8">
                            <dt class="text-base/7 font-semibold text-gray-900 lg:col-span-5">Can I collaborate with other researchers?</dt>
                            <dd class="mt-4 lg:col-span-7 lg:mt-0">
                                <p class="text-base/7 text-gray-600">Yes, nmrXiv provides robust collaboration features. You can share your data with specific researchers, create collaborative projects, and work together on data analysis in real-time. All collaborations are managed through secure access controls.</p>
                            </dd>
                        </div>
                        <div class="py-8 first:pt-0 last:pb-0 lg:grid lg:grid-cols-12 lg:gap-8">
                            <dt class="text-base/7 font-semibold text-gray-900 lg:col-span-5">How does the platform ensure FAIR data principles?</dt>
                            <dd class="mt-4 lg:col-span-7 lg:mt-0">
                                <p class="text-base/7 text-gray-600">Our platform is built on FAIR data principles: Findable (through comprehensive metadata and search), Accessible (via secure, controlled access), Interoperable (supporting multiple formats and standards), and Reusable (with detailed documentation and provenance tracking).</p>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- CTA section -->
            <div class="relative -z-10 mt-32 px-6 lg:px-8">
                <div class="absolute inset-x-0 top-1/2 -z-10 flex -translate-y-1/2 transform-gpu justify-center overflow-hidden blur-3xl sm:top-auto sm:right-[calc(50%-6rem)] sm:bottom-0 sm:translate-y-0 sm:transform-gpu sm:justify-end" aria-hidden="true">
                    <div class="aspect-1108/632 w-277 flex-none bg-linear-to-r from-[#ff80b5] to-[#9089fc] opacity-25" style="clip-path: polygon(73.6% 48.6%, 91.7% 88.5%, 100% 53.9%, 97.4% 18.1%, 92.5% 15.4%, 75.7% 36.3%, 55.3% 52.8%, 46.5% 50.9%, 45% 37.4%, 50.3% 13.1%, 21.3% 36.2%, 0.1% 0.1%, 5.4% 49.1%, 21.4% 36.4%, 58.9% 100%, 73.6% 48.6%)"></div>
                </div>
                <div class="mx-auto text-center">
                    <h2 class="text-4xl font-semibold tracking-tight text-balance text-gray-900 sm:text-5xl">Start managing your NMR data today</h2>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="/register" class="rounded-md bg-blue-700 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Get started</a>
                        <a href="#" class="text-sm/6 font-semibold text-gray-900">Contact us <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative mx-auto mt-32 max-w-7xl px-6 lg:px-8">
            <div class="border-t border-gray-900/10 py-12">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <img class="h-10 w-auto" src="/img/logo.svg" alt="NFDI4Chem NMR Platform">
                    </div>
                    <div class="text-right space-y-2">
                        <p class="text-sm text-gray-500">© {{ date('Y') }} NFDI4Chem NMR Platform. All rights reserved.</p>
                        <div class="flex items-center space-x-6 text-sm text-gray-500">
                            <span>Part of the <a href="https://www.nfdi4chem.de/" target="_blank" class="text-primary-600 hover:text-primary-700 underline decoration-1">NFDI4Chem</a> initiative</span>
                            <span class="h-1 w-1 rounded-full bg-gray-300"></span>
                            <span>Funded by the <a href="https://www.dfg.de/en/" target="_blank" class="text-primary-600 hover:text-primary-700 underline decoration-1">German Research Foundation (DFG)</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</x-guest-layout>