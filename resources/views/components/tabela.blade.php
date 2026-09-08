<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col"
    :class="telaCheia ? 'h-full border-none rounded-none shadow-none' : ''">

    <!-- Topo: Busca Global + Ações -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 space-y-3">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <form action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                method="GET" class="relative w-full sm:w-96">
                @if (request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                @endif
                @if (!empty($colunaDuplicada))
                    <input type="hidden" name="duplicate_column" value="{{ $colunaDuplicada }}">
                @endif
                @foreach ($filtrosColuna ?? [] as $col => $val)
                    <input type="hidden" name="filters[{{ $col }}]" value="{{ $val }}">
                @endforeach

                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ $termoBusca ?? '' }}"
                    placeholder="Busca geral em todas colunas..."
                    class="w-full pl-9 pr-10 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500">
                @if (!empty($termoBusca))
                    <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line"></i>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 flex-wrap">
                <button @click="painelDuplicadosOpen = !painelDuplicadosOpen; painelFiltrosOpen = false"
                    class="px-3 py-2 text-sm font-medium rounded-lg border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 flex items-center gap-2 transition"
                    :class="{ 'bg-amber-500 text-white dark:bg-amber-600 dark:text-white': painelDuplicadosOpen || {{ !empty($colunaDuplicada) ? 'true' : 'false' }} }">
                    <i class="ri-file-copy-line text-amber-500" :class="{ 'text-white': painelDuplicadosOpen || {{ !empty($colunaDuplicada) ? 'true' : 'false' }} }"></i>
                    <span>Detectar Duplicados</span>
                    @if (!empty($colunaDuplicada))
                        <span class="bg-amber-700 text-white text-xs px-2 py-0.5 rounded-full font-bold">Ativo</span>
                    @endif
                </button>

                <button @click="painelFiltrosOpen = !painelFiltrosOpen; painelDuplicadosOpen = false"
                    class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 transition"
                    :class="{ 'bg-brand-500 text-white dark:bg-brand-600 dark:text-white': painelFiltrosOpen }">
                    <i class="ri-filter-3-line text-brand-500" :class="{ 'text-white': painelFiltrosOpen }"></i>
                    <span>Filtros</span>
                    @if (!empty($filtrosColuna))
                        <span class="bg-brand-600 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                            {{ count($filtrosColuna) }}
                        </span>
                    @endif
                </button>

                <button @click="telaCheia = !telaCheia"
                    class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-700 transition flex items-center gap-1 text-sm font-medium">
                    <i :class="telaCheia ? 'ri-fullscreen-exit-line text-brand-500' : 'ri-fullscreen-line'" class="text-lg"></i>
                    <span x-text="telaCheia ? 'Sair' : 'Expandir'" class="hidden md:inline"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela de Dados -->
    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-xs tracking-wider border-b border-gray-200 dark:border-gray-700 select-none sticky top-0 bg-white dark:bg-gray-800 z-10">
                <tr>
                    <th class="px-4 py-3 font-semibold text-center w-28">Ações</th>
                    @foreach ($colunas as $coluna)
                        @php
                            $isSorted = ($sortColuna ?? '') === $coluna;
                            $nextDirection = $isSorted && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <th class="px-6 py-3 font-semibold whitespace-nowrap hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                            <a href="{{ route('visualizar', array_merge(request()->query(), ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela, 'sort' => $coluna, 'direction' => $nextDirection])) }}"
                                class="flex items-center gap-1.5 w-full h-full">
                                <span>{{ $coluna }}</span>
                                <i class="{{ $isSorted ? (($direction ?? 'asc') === 'asc' ? 'ri-arrow-up-line text-brand-500 font-bold' : 'ri-arrow-down-line text-brand-500 font-bold') : 'ri-arrow-up-down-line text-gray-400 opacity-40 hover:opacity-100' }}"></i>
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($dados as $linha)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="registroSelecionado = {{ json_encode($linha) }}" title="Ver Detalhes"
                                    class="p-1.5 text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/40 rounded-lg transition">
                                    <i class="ri-eye-line text-base"></i>
                                </button>
                                <button @click="registroParaEditar = JSON.parse(JSON.stringify({{ json_encode($linha) }}))" title="Editar Registro"
                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                    :class="{ 'opacity-40 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                    class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/40 rounded-lg transition">
                                    <i class="ri-pencil-line text-base"></i>
                                </button>
                                <button @click="registroParaDeletar = {{ json_encode($linha) }}" title="Excluir Registro"
                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                    :class="{ 'opacity-40 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-lg transition">
                                    <i class="ri-delete-bin-line text-base"></i>
                                </button>
                            </div>
                        </td>
                        @foreach ((array) $linha as $chaveCol => $valor)
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                @if (is_null($valor))
                                    <span class="text-xs text-gray-400 italic">null</span>
                                @elseif(is_bool($valor))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $valor ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $valor ? 'Verdadeiro' : 'Falso' }}
                                    </span>
                                @elseif(is_array($valor) || is_object($valor))
                                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded">{{ json_encode($valor) }}</span>
                                @else
                                    {{ $valor }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($colunas) > 0 ? count($colunas) + 1 : 1 }}" class="px-6 py-12 text-center text-gray-400">
                            <i class="ri-inbox-line text-4xl block mb-2"></i>
                            Nenhum registro encontrado nesta tabela.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        @if (method_exists($dados, 'links'))
            {{ $dados->links() }}
        @endif
    </div>
</div>