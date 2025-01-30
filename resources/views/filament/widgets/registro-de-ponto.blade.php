<x-filament-widgets::widget>
    @if(auth()->user()->can('widget_RegistroDePonto'))
    <x-filament::section>
        <div class="grid grid-cols-2 gap-2 -m-2">
            <x-filament::button
                tag="a"
                href="{{ route('filament.app.pages.registro-de-ponto.registro') }}"
                class="bg-primary-200 text-primary-800 w-full rounded-xl"
            >
                <div class="flex items-center p-2 space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    <div class="font-bold">Registrar Ponto</div>
                </div>
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ route('filament.app.pages.registro-de-ponto.cartao-de-ponto') }}"
                outlined
                class="w-full">
                Meu cartão de ponto
            </x-filament::button>
        </div>
    </x-filament::section>
    @endif
</x-filament-widgets::widget>
