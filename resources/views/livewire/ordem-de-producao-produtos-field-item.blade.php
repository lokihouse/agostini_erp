<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
    <td class="px-4 py-4">
        <x-filament::icon-button
            icon="heroicon-o-trash"
            size="xs"
            color="danger"
            wire:click="deleteProduto"
        />
    </td>
    <td class="px-2 py-4">
        {{ $produto['quantidade'] }}
    </td>
    <th scope="row" class="px-2 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
        {{ $produto['produto_nome'] }}
    </th>
    {{ json_encode($produto) }}
</tr>
