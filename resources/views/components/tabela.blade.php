<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden flex flex-col transition"
    x-data="{
        telaCheia: false,
        painelFiltrosOpen: false,
        painelDuplicadosOpen: false,
        registroSelecionado: null,
        registroParaEditar: null,
        registroParaDeletar: null,
        detalhesOpen: false
    }"
    :class="telaCheia ? 'h-full border-none rounded-none shadow-none' : ''">

    <!-- Topo: Busca Global + Ações Minimalistas -->
    <div class="p-3.5 border-b border-slate-200/80 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <form action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                method="GET" class="relative w-full sm:w-80">
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

                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $termoBusca ?? '' }}"
                    placeholder="Buscar em todas as colunas..."
                    class="w-full pl-8 pr-8 py-1.5 text-xs bg-slate-100/70 dark:bg-slate-800/60 border border-transparent focus:border-slate-300 dark:focus:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none transition">
                @if (!empty($termoBusca))
                    <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="ri-close-line text-sm"></i>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Detectar Duplicados -->
                <button @click="painelDuplicadosOpen = !painelDuplicadosOpen; painelFiltrosOpen = false"
                    title="Detectar Duplicados"
                    class="px-2.5 py-1.5 text-xs font-medium rounded-lg border transition flex items-center gap-1.5 {{ !empty($colunaDuplicada) ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i class="ri-file-copy-line text-xs {{ !empty($colunaDuplicada) ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }}"></i>
                    <span>Duplicados</span>
                    @if (!empty($colunaDuplicada))
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    @endif
                </button>

                <!-- Filtros -->
                <button @click="painelFiltrosOpen = !painelFiltrosOpen; painelDuplicadosOpen = false"
                    title="Filtros"
                    class="px-2.5 py-1.5 text-xs font-medium rounded-lg border transition flex items-center gap-1.5 {{ !empty($filtrosColuna) ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300 dark:border-brand-800' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <i class="ri-filter-3-line text-xs {{ !empty($filtrosColuna) ? 'text-brand-600 dark:text-brand-400' : 'text-slate-400' }}"></i>
                    <span>Filtros</span>
                    @if (!empty($filtrosColuna))
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-semibold bg-brand-600 text-white">
                            {{ count($filtrosColuna) }}
                        </span>
                    @endif
                </button>

                <!-- Tela Cheia -->
                <button @click="telaCheia = !telaCheia"
                    title="Expandir visualização"
                    class="p-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i :class="telaCheia ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line'" class="text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Detalhes Drawer Lateral -->
    <div x-show="detalhesOpen" x-cloak
         class="fixed top-0 right-0 h-full w-96 bg-white dark:bg-slate-900 shadow-xl z-50 border-l border-slate-200 dark:border-slate-800 flex flex-col transition-all">
        <div class="h-14 px-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <span class="font-medium text-xs text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ri-eye-line text-slate-400"></i> Detalhes do Registro
            </span>
            <button @click="detalhesOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-3">
            <template x-if="registroSelecionado">
                <div class="space-y-2">
                    <template x-for="(valor, chave) in registroSelecionado" :key="chave">
                        <div class="py-1.5 border-b border-slate-100 dark:border-slate-800/60 last:border-0">
                            <div class="text-[10px] font-mono text-slate-400 dark:text-slate-500 uppercase" x-text="chave"></div>
                            <div class="text-xs font-mono text-slate-800 dark:text-slate-200 break-all mt-0.5" x-text="valor === null ? 'null' : valor"></div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- Filtros Drawer Lateral -->
    <div x-show="painelFiltrosOpen" x-cloak
         class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-slate-900 shadow-xl z-50 border-l border-slate-200 dark:border-slate-800 flex flex-col transition-all">
        <div class="h-14 px-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <span class="font-medium text-xs text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ri-filter-3-line text-slate-400"></i> Filtros por Coluna
            </span>
            <button @click="painelFiltrosOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <form action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}" method="GET" class="flex-1 overflow-y-auto p-4 space-y-3">
            <input type="hidden" name="search" value="{{ $termoBusca ?? '' }}">
            <input type="hidden" name="sort" value="{{ $sortColuna ?? '' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">
            <input type="hidden" name="duplicate_column" value="{{ $colunaDuplicada ?? '' }}">

            @foreach ($colunas as $coluna)
                <div class="space-y-1">
                    <label class="text-[10px] font-mono text-slate-500 dark:text-slate-400 uppercase">{{ $coluna }}</label>
                    <input type="text" name="filters[{{ $coluna }}]" value="{{ $filtrosColuna[$coluna] ?? '' }}"
                           placeholder="Filtrar..."
                           class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:border-slate-400 dark:focus:border-slate-600 transition">
                </div>
            @endforeach

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 py-1.5 px-3 rounded-lg text-xs font-medium transition">
                    Aplicar Filtros
                </button>
                <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                   class="px-3 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition text-center">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Detectar Duplicados Drawer Lateral -->
    <div x-show="painelDuplicadosOpen" x-cloak
         class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-slate-900 shadow-xl z-50 border-l border-slate-200 dark:border-slate-800 flex flex-col transition-all">
        <div class="h-14 px-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <span class="font-medium text-xs text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ri-file-copy-line text-slate-400"></i> Verificar Duplicados
            </span>
            <button @click="painelDuplicadosOpen = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>
        <form action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}" method="GET" class="p-5 space-y-4">
            <input type="hidden" name="search" value="{{ $termoBusca ?? '' }}">
            <input type="hidden" name="sort" value="{{ $sortColuna ?? '' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">
            @foreach ($filtrosColuna ?? [] as $col => $val)
                <input type="hidden" name="filters[{{ $col }}]" value="{{ $val }}">
            @endforeach

            <div class="space-y-1.5">
                <label class="text-xs font-medium text-slate-600 dark:text-slate-400">Selecionar coluna:</label>
                <select name="duplicate_column" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none">
                    <option value="">-- Selecione uma coluna --</option>
                    @foreach ($colunas as $coluna)
                        <option value="{{ $coluna }}" {{ $colunaDuplicada === $coluna ? 'selected' : '' }}>{{ $coluna }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 py-1.5 px-3 rounded-lg text-xs font-medium transition">
                    Verificar
                </button>
                <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                   class="px-3 py-1.5 text-xs font-medium text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition text-center">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Dados Minimalista -->
    <div class="overflow-x-auto flex-1 relative">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="sticky top-0 z-20 bg-slate-50 dark:bg-slate-900/90 backdrop-blur-sm text-slate-500 dark:text-slate-400 border-b border-slate-200/80 dark:border-slate-800 select-none">
                <tr>
                    <th class="sticky left-0 z-30 bg-slate-50 dark:bg-slate-900 px-3 py-2.5 font-medium text-center w-20 border-r border-slate-200/80 dark:border-slate-800 text-[11px]">
                        Ações
                    </th>
                    @foreach ($colunas as $coluna)
                        @php
                            $isSorted = ($sortColuna ?? '') === $coluna;
                            $nextDirection = $isSorted && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <th class="px-3.5 py-2.5 font-medium whitespace-nowrap hover:text-slate-900 dark:hover:text-white transition cursor-pointer text-[11px]">
                            <a href="{{ route('visualizar', array_merge(request()->query(), ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela, 'sort' => $coluna, 'direction' => $nextDirection])) }}"
                                class="flex items-center gap-1.5 w-full h-full group font-mono">
                                <span class="{{ $isSorted ? 'text-slate-900 dark:text-white font-semibold' : '' }}">{{ $coluna }}</span>
                                <i class="{{ $isSorted ? (($direction ?? 'asc') === 'asc' ? 'ri-arrow-up-s-line text-slate-900 dark:text-white font-bold' : 'ri-arrow-down-s-line text-slate-900 dark:text-white font-bold') : 'ri-arrow-up-down-line text-slate-300 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition' }}"></i>
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                @forelse($dados as $linha)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors group">
                        <!-- Coluna Fixa de Ações -->
                        <td class="sticky left-0 z-10 bg-white group-hover:bg-slate-50 dark:bg-slate-900 dark:group-hover:bg-slate-800/80 px-2 py-1.5 text-center whitespace-nowrap border-r border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="registroSelecionado = {{ json_encode($linha) }}; detalhesOpen = true" title="Visualizar"
                                    class="p-1 text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded transition">
                                    <i class="ri-eye-line text-xs"></i>
                                </button>
                                <button @click="registroParaEditar = JSON.parse(JSON.stringify({{ json_encode($linha) }}))" title="Editar"
                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                    :class="{ 'opacity-20 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                    class="p-1 text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded transition">
                                    <i class="ri-pencil-line text-xs"></i>
                                </button>
                                <button @click="registroParaDeletar = {{ json_encode($linha) }}" title="Excluir"
                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                    :class="{ 'opacity-20 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                    class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded transition">
                                    <i class="ri-delete-bin-line text-xs"></i>
                                </button>
                            </div>
                        </td>
                        @foreach ((array) $linha as $chaveCol => $valor)
                            <td class="px-3.5 py-2 whitespace-nowrap text-slate-700 dark:text-slate-300 text-xs max-w-xs truncate">
                                @if (is_null($valor))
                                    <span class="text-[10px] text-slate-400 dark:text-slate-600">null</span>
                                @elseif(is_bool($valor))
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-sans font-medium {{ $valor ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ $valor ? 'true' : 'false' }}
                                    </span>
                                @elseif(is_array($valor) || is_object($valor))
                                    <span class="text-[11px] text-slate-500 font-mono">{{ json_encode($valor) }}</span>
                                @else
                                    <span title="{{ $valor }}">{{ $valor }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($colunas) > 0 ? count($colunas) + 1 : 1 }}" class="px-6 py-12 text-center text-slate-400">
                            <i class="ri-inbox-line text-2xl block mb-1 text-slate-300 dark:text-slate-600"></i>
                            Nenhum registro encontrado nesta tabela.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginação Minimalista -->
    <div class="px-4 py-3 border-t border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900">
        @if (method_exists($dados, 'links'))
            {{ $dados->links() }}
        @endif
    </div>
</div>
