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

    <!-- Painel de Filtros Avançados -->
    <div x-show="painelFiltrosOpen" x-cloak
         class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-2xl z-50 border-l border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 transform">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ri-filter-3-line text-brand-500"></i> Filtros Avançados
            </h3>
            <button @click="painelFiltrosOpen = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <form action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}" method="GET" class="flex-1 overflow-y-auto p-4 space-y-4">
            <input type="hidden" name="search" value="{{ $termoBusca ?? '' }}">
            <input type="hidden" name="sort" value="{{ $sortColuna ?? '' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">
            <input type="hidden" name="duplicate_column" value="{{ $colunaDuplicada ?? '' }}">

            @foreach ($colunas as $coluna)
                <div class="space-y-1">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $coluna }}</label>
                    <input type="text" name="filters[{{ $coluna }}]" value="{{ $filtrosColuna[$coluna] ?? '' }}"
                           placeholder="Filtrar..."
                           class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
            @endforeach

            <div class="pt-4 flex gap-2">
                <button type="submit" class="flex-1 bg-brand-500 hover:bg-brand-600 text-white py-2 px-4 rounded-lg text-sm font-bold transition">
                    Aplicar
                </button>
                <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Painel de Detecção de Duplicados -->
    <div x-show="painelDuplicadosOpen" x-cloak
         class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-2xl z-50 border-l border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 transform">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="ri-file-copy-line text-amber-500"></i> Detectar Duplicados
            </h3>
            <button @click="painelDuplicadosOpen = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>
        <form action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}" method="GET" class="p-6 space-y-6">
            <input type="hidden" name="search" value="{{ $termoBusca ?? '' }}">
            <input type="hidden" name="sort" value="{{ $sortColuna ?? '' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">
            @foreach ($filtrosColuna ?? [] as $col => $val)
                <input type="hidden" name="filters[{{ $col }}]" value="{{ $val }}">
            @endforeach

            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Selecione a coluna para verificar duplicados:</label>
                <select name="duplicate_column" class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="">-- Selecione --</option>
                    @foreach ($colunas as $coluna)
                        <option value="{{ $coluna }}" {{ $colunaDuplicada === $coluna ? 'selected' : '' }}>{{ $coluna }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 flex gap-2">
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2 px-4 rounded-lg text-sm font-bold transition">
                    Detectar
                </button>
                <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Dados Refatorada -->
    <div class="overflow-x-auto flex-1 relative border-t border-gray-200 dark:border-gray-700">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="sticky top-0 z-20 bg-gray-100/90 dark:bg-gray-800/95 backdrop-blur-sm text-gray-600 dark:text-gray-300 uppercase tracking-wider text-[11px] border-b border-gray-200 dark:border-gray-700 select-none shadow-sm">
                <tr>
                    <th class="sticky left-0 z-30 bg-gray-100 dark:bg-gray-800 px-3 py-2.5 font-bold text-center w-24 border-r border-gray-200 dark:border-gray-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                        Ações
                    </th>
                    @foreach ($colunas as $coluna)
                        @php
                            $isSorted = ($sortColuna ?? '') === $coluna;
                            $nextDirection = $isSorted && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <th class="px-4 py-2.5 font-semibold whitespace-nowrap hover:bg-gray-200/60 dark:hover:bg-gray-700/60 transition cursor-pointer">
                            <a href="{{ route('visualizar', array_merge(request()->query(), ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela, 'sort' => $coluna, 'direction' => $nextDirection])) }}"
                                class="flex items-center gap-1.5 w-full h-full group">
                                <span class="{{ $isSorted ? 'text-brand-600 dark:text-brand-400 font-bold' : '' }}">{{ $coluna }}</span>
                                <i class="{{ $isSorted ? (($direction ?? 'asc') === 'asc' ? 'ri-arrow-up-line text-brand-600 dark:text-brand-400 font-bold' : 'ri-arrow-down-line text-brand-600 dark:text-brand-400 font-bold') : 'ri-arrow-up-down-line text-gray-400 opacity-0 group-hover:opacity-100 transition' }}"></i>
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-mono">
                @forelse($dados as $linha)
                    <tr class="hover:bg-brand-50/40 dark:hover:bg-brand-950/20 even:bg-gray-50/40 dark:even:bg-gray-800/30 transition-colors group">
                        <td class="sticky left-0 z-10 bg-white group-even:bg-gray-50/90 group-hover:bg-brand-50/90 dark:bg-gray-800 dark:group-even:bg-gray-800/90 dark:group-hover:bg-gray-800 px-2 py-2 text-center whitespace-nowrap border-r border-gray-200 dark:border-gray-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center justify-center gap-0.5">
                                <button @click="registroSelecionado = {{ json_encode($linha) }}" title="Visualizar"
                                    class="p-1 text-gray-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/40 rounded transition">
                                    <i class="ri-eye-line text-sm"></i>
                                </button>
                                <button @click="registroParaEditar = JSON.parse(JSON.stringify({{ json_encode($linha) }}))" title="Editar"
                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                    :class="{ 'opacity-30 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                    class="p-1 text-gray-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/40 rounded transition">
                                    <i class="ri-pencil-line text-sm"></i>
                                </button>
                                <button @click="registroParaDeletar = {{ json_encode($linha) }}" title="Excluir"
                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                    :class="{ 'opacity-30 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                    class="p-1 text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/40 rounded transition">
                                    <i class="ri-delete-bin-line text-sm"></i>
                                </button>
                            </div>
                        </td>
                        @foreach ((array) $linha as $chaveCol => $valor)
                            <td class="px-4 py-2 whitespace-nowrap text-gray-800 dark:text-gray-200 text-xs max-w-xs truncate selection:bg-brand-200">
                                @if (is_null($valor))
                                    <span class="inline-block px-1.5 py-0.2 text-[10px] font-sans font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700">null</span>
                                @elseif(is_bool($valor))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-sans font-semibold {{ $valor ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800' }}">
                                        {{ $valor ? 'TRUE' : 'FALSE' }}
                                    </span>
                                @elseif(is_array($valor) || is_object($valor))
                                    <span class="text-[11px] bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">{{ json_encode($valor) }}</span>
                                @else
                                    <span title="{{ $valor }}">{{ $valor }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($colunas) > 0 ? count($colunas) + 1 : 1 }}" class="px-6 py-12 text-center text-gray-400">
                            <i class="ri-inbox-line text-3xl block mb-1"></i>
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