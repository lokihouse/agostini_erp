@php
    use App\Models\Produto;
    $state = $getState();

    if($state){
        $state = array_map(function ($std){
            $std['produto'] = Produto::query()->where('id', $std['produto_id'])->first()->toArray();
            return $std;
        }, $state);
    }else{
        $state = [];
    }
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.{{ $applyStateBindingModifiers("entangle('{$getStatePath()}')") }} }"
         class="-mx-4 -mt-4">
        <div class="relative overflow-x-auto text-xs text-left">
            <table class="w-full text-gray-500 dark:text-gray-400 mb-1">
                <thead class="text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" rowspan="2" class="px-2 py-1 border-e w-1">
                        &nbsp;
                    </th>
                    <th scope="col" rowspan="2" class="px-2 py-1 border-e w-[50px]">
                        qnt.
                    </th>
                    <th scope="col" rowspan="2" class="px-2 py-1 border-e">
                        Produto
                    </th>
                    <th scope="col" colspan="5" class="px-2 py-1 text-center border-b">
                        Volumes
                    </th>
                </tr>
                <tr class="border-b">
                    <th scope="col" class="px-2 py-1 w-[50px] border-e text-center">
                        L
                    </th>
                    <th scope="col" class="px-2 py-1 w-[50px] border-e text-center">
                        A
                    </th>
                    <th scope="col" class="px-2 py-1 w-[50px] border-e text-center">
                        C
                    </th>
                    <th scope="col" class="px-2 py-1 w-[50px] text-center">
                        P
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($state as $idx => $produto)
                    @php
                    $volumes = json_decode($produto['produto']['volumes'], true) ?? null;
                    @endphp
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <td class="px-2 py-1 text-center border-e">
                            <x-filament::icon-button
                                icon="heroicon-o-trash"
                                size="xs"
                                color="danger"
                                wire:click="dispatchFormEvent('myComponent::updated', '{{$idx}}')"
                            />
                        </td>
                        <td class="px-2 py-1 text-center border-e">
                            {{ $produto['quantidade'] }}
                        </td>
                        <td class="px-2 py-1 border-e">
                            {{ $produto['produto']['nome'] }}
                        </td>
                        <td class="px-2 py-1 border-e">
                            @if($volumes)
                                @foreach(array_map(fn($v) => $v['largura'], $volumes) as $largura)
                                    <div class="py-1 text-center">
                                        {{ $largura }}
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-2 py-1 border-e">
                            @if($volumes)
                                @foreach(array_map(fn($v) => $v['altura'], $volumes) as $altura)
                                    <div class="py-1 text-center">
                                        {{ $altura }}
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-2 py-1 border-e">
                            @if($volumes)
                                @foreach(array_map(fn($v) => $v['comprimento'], $volumes) as $comprimento)
                                    <div class="py-1 text-center">
                                        {{ $comprimento }}
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-2 py-1">
                            @if($volumes)
                                @foreach(array_map(fn($v) => $v['peso'], $volumes) as $peso)
                                    <div class="py-1 text-center">
                                        {{ $peso }}
                                    </div>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-dynamic-component>
