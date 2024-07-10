@props([
    'origin' => false,
    'intervalo' => '00/00/0000 - 00/00/0000',
])

@if($origin)
    <html lang="en">
    <head>
        <title>Invoice</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body>
@endif
    <div>
        <div class='fi-topbar sticky top-0 z-20 overflow-x-clip'>
            <nav
                class="flex h-16 items-center gap-x-4 bg-gray-200 px-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-6 lg:px-8"
            >
                <div class="me-6 hidden lg:flex">
                    <div class="fi-logo flex text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white">
                        AGOSTINI
                    </div>
                </div>

                <div x-persist="topbar.end" class="ms-auto text-right" >
                    <div class="text-2xl font-medium">
                        Cartão de Ponto
                    </div>
                    <div>
                        {{ $intervalo }}
                    </div>
                </div>
            </nav>
        </div>
        <div class="bg-red-400">
            <x-filament::section>
                <x-slot name="heading">
                    João Da Silva
                </x-slot>

                {{-- Content --}}
            </x-filament::section>
        </div>
    </div>
@if($origin)
    </body>
</html>
@endif
