<div
    class="fi-ta-content border-2 border-gray-100 rounded-xl relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
    <table
        class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
        <thead class="divide-y divide-gray-200 dark:divide-white/5">
        <tr class="bg-gray-50 dark:bg-white/5">
            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-quantidade w-1">
                <span
                    class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                    <span
                        class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Quant.
                    </span>
                </span>
            </th>

            <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-produto.nome">
                <span
                    class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                    <span
                        class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        Descrição
                    </span>
                </span>
            </th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
        @foreach($producao as $prod)
            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75">
                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-quantidade">
                    <div class="fi-ta-col-wrp">
                        <div
                            class="flex w-full disabled:pointer-events-none justify-start text-start">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                <div class="flex ">
                                    <div class="flex max-w-max">
                                        <div
                                            class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                <span
                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $prod->quantidade }}
                                                </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>

                <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-produto.nome">
                    <div class="fi-ta-col-wrp">
                        <div
                            class="flex w-full disabled:pointer-events-none justify-start text-start">
                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                <div class="flex ">
                                    <div class="flex max-w-max" style="">
                                        <div
                                            class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                <span
                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white">
                                                     {{ $prod->descricao }}
                                                </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
