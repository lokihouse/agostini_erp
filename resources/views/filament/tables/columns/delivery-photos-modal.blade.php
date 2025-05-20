{{-- resources/views/filament/tables/columns/delivery-photos-modal.blade.php --}}
<div>
    @if (!empty($photos) && is_array($photos))
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-4">
            @foreach ($photos as $photoPath)
                @if($photoPath)
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded-lg shadow">
                        <img src="{{ Storage::url($photoPath) }}"
                             alt="Foto da Entrega"
                             class="w-full h-auto max-h-[70vh] object-contain rounded-md cursor-pointer"
                             onclick="this.requestFullscreen ? this.requestFullscreen() : this.webkitRequestFullscreen ? this.webkitRequestFullscreen() : null;"
                             title="Clique para tela cheia (se suportado)">
                    </div>
                @endif
            @endforeach
        </div>
        @if (count(array_filter($photos)) === 0)
            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Nenhuma foto para exibir.</p>
        @endif
    @else
        <p class="text-center text-gray-500 dark:text-gray-400 py-4">Nenhuma foto disponível.</p>
    @endif
</div>
