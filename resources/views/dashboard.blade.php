<!DOCTYPE html>
<html lang="pt-BR" x-data="{
    darkMode: {{ $darkMode ? 'true' : 'false' }},
    sidebarOpen: true,
    registroSelecionado: null,
    registroParaEditar: null,
    registroParaDeletar: null,
    painelFiltrosOpen: {{ !empty($filtrosColuna) ? 'true' : 'false' }},
    painelDuplicadosOpen: {{ !empty($colunaDuplicada) ? 'true' : 'false' }},
    telaCheia: false,
    historicoTrabalhador: [],
    carregandoHistorico: false,
    modalHistoricoOpen: false,
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        document.cookie = 'dark_mode=' + (this.darkMode ? '1' : '0') + '; path=/; max-age=' + (60 * 60 * 24 * 365);
    },
    async carregarHistoricoTrabalhador(id) {
        if (!id) return;
        this.carregandoHistorico = true;
        this.modalHistoricoOpen = true;
        this.historicoTrabalhador = [];

        try {
            const encodedId = encodeURIComponent(String(id).trim());
            const response = await fetch(`{{ url('/') }}/{{ $banco }}/trabalhador/${encodedId}/historico`);
            const result = await response.json();
            if (result.success) {
                this.historicoTrabalhador = result.data;
            } else {
                alert('Erro ao carregar histórico: ' + (result.error || 'Erro desconhecido'));
                this.modalHistoricoOpen = false;
            }
        } catch (error) {
            alert('Erro na requisição: ' + error.message);
            this.modalHistoricoOpen = false;
        } finally {
            this.carregandoHistorico = false;
        }
    }
}" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralizador - Visualizador de Dados</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            500: '#0284c7',
                            600: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Remixicons Font -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased font-sans min-h-screen flex flex-col">

    <!-- ALERTA DE SUCESSO OU ERRO -->
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-4 right-4 z-50 bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transition">
            <i class="ri-checkbox-circle-fill text-xl"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR LATERAL -->
        <aside x-show="!telaCheia" :class="sidebarOpen ? 'w-64' : 'w-20'"
            class="bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 flex flex-col z-20">

            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3 overflow-hidden" x-show="sidebarOpen">
                    <div class="p-2 bg-brand-500 text-white rounded-lg">
                        <i class="ri-database-2-line text-xl"></i>
                    </div>
                    <span class="font-bold text-lg text-gray-900 dark:text-white truncate">Centralizador</span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i :class="sidebarOpen ? 'ri-indent-decrease' : 'ri-indent-increase'" class="text-xl"></i>
                </button>
            </div>

            <!-- Navegação de Schemas e Tabelas -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-3" x-data="{ buscaTabelaSidebar: '' }">
                @if (!empty($menuFasesEsocial))
                    <!-- CABEÇALHO DO MÓDULO ESOCIAL -->
                    <div x-show="sidebarOpen"
                        class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 border-b border-gray-200 dark:border-gray-700 mb-2 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="ri-shield-check-line text-brand-500"></i> Eventos eSocial
                        </span>
                        <span
                            class="bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 px-1.5 py-0.5 rounded font-mono text-[10px]">MOS</span>
                    </div>

                    <!-- CAMPO DE BUSCA RÁPIDA DE TABELAS -->
                    <div x-show="sidebarOpen" class="mb-3 px-1">
                        <div class="relative">
                            <i
                                class="ri-search-2-line absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" x-model="buscaTabelaSidebar"
                                placeholder="Filtrar eventos (ex: 2200)..."
                                class="w-full pl-7 pr-3 py-1 text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-md focus:outline-none focus:ring-1 focus:ring-brand-500 text-gray-700 dark:text-gray-300">
                        </div>
                    </div>

                    @foreach ($menuFasesEsocial as $faseNome => $faseInfo)
                        @php
                            $tabelasFase = $faseInfo['tabelas'] ?? [];
                            $icone = $faseInfo['icone'] ?? 'ri-folder-line';
                            $hasActiveTable = collect($tabelasFase)->contains(fn($i) => $i->table_name === $tabela);
                            $isS3000 = str_contains($faseNome, 'S-3000') || str_contains($faseNome, 'Exclusão');
                            $isSST = str_contains($faseNome, 'SST');
                            $isTabela = str_contains($faseNome, 'Tabela');
                            $isNaoPeriodico = str_contains($faseNome, 'Não Periódico');
                        @endphp
                        <div x-data="{ open: {{ $hasActiveTable ? 'true' : 'false' }} }" class="mb-2">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-2.5 py-2 text-xs font-bold rounded-lg transition {{ $isS3000 ? 'bg-red-50/80 dark:bg-red-950/30 text-red-700 dark:text-red-300 border border-red-200/70 dark:border-red-900/40' : ($isSST ? 'bg-emerald-50/80 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-900/40' : ($isTabela ? 'bg-blue-50/80 dark:bg-blue-950/30 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-900/40' : ($isNaoPeriodico ? 'bg-indigo-50/80 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-900/40' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'))) }}">
                                <span class="flex items-center gap-1.5 truncate">
                                    <i class="{{ $icone }} text-sm"></i>
                                    <span x-show="sidebarOpen" class="truncate">{{ $faseNome }}</span>
                                </span>
                                <span class="flex items-center gap-1" x-show="sidebarOpen">
                                    <span
                                        class="text-[10px] px-1.5 py-0.2 rounded-full bg-white/80 dark:bg-gray-900/60 font-mono font-normal">({{ count($tabelasFase) }})</span>
                                    <i :class="open ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'"></i>
                                </span>
                            </button>

                            <div x-show="open && sidebarOpen" x-collapse class="mt-1 pl-2 space-y-0.5">
                                @foreach ($tabelasFase as $item)
                                    <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => 'esocial', 'tabela' => $item->table_name]) }}"
                                        x-show="!buscaTabelaSidebar || '{{ strtolower($item->table_name) }}'.includes(buscaTabelaSidebar.toLowerCase())"
                                        class="block px-3 py-1.5 text-xs rounded-md font-mono transition truncate {{ $tabela === $item->table_name ? 'bg-brand-500 text-white font-bold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        {{ $item->table_name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach ($menuSchemas as $nomeSchema => $tabelas)
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <span class="flex items-center gap-2">
                                    <i class="ri-folder-3-line text-brand-500"></i>
                                    <span x-show="sidebarOpen">{{ strtoupper($nomeSchema) }}
                                        ({{ $tabelas->count() }})
                                    </span>
                                </span>
                                <i x-show="sidebarOpen"
                                    :class="open ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'"></i>
                            </button>

                            <div x-show="open && sidebarOpen" x-collapse class="mt-1 pl-3 space-y-1">
                                @foreach ($tabelas as $item)
                                    <a href="{{ route('visualizar', ['banco' => $banco, 'schema' => $nomeSchema, 'tabela' => $item->table_name]) }}"
                                        class="block px-3 py-1.5 text-sm rounded-md transition text-ellipsis overflow-hidden whitespace-nowrap {{ $tabela === $item->table_name && $schema === $nomeSchema ? 'bg-brand-500 text-white font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        {{ $item->table_name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </nav>
        </aside>

        <!-- ÁREA DE CONTEÚDO PRINCIPAL -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- HEADER SUPERIOR -->
            <header x-show="!telaCheia"
                class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-10">
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <i class="ri-database-2-line"></i>
                    <span>Schema: <strong
                            class="text-gray-800 dark:text-gray-200 font-medium">{{ $schema ?? 'public' }}</strong></span>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white font-semibold">{{ $tabela ?? 'Tabela' }}</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <i class="ri-server-line text-gray-400"></i>
                        <select onchange="location = this.value;"
                            class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 text-sm rounded-lg p-2 focus:ring-brand-500">
                            @foreach ($bancosDisponiveis as $key => $nome)
                                <option value="{{ route('visualizar', ['banco' => $key]) }}"
                                    {{ $banco === $key ? 'selected' : '' }}>
                                    {{ $nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button @click="toggleDarkMode()"
                        class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                        title="Alternar Tema">
                        <i :class="darkMode ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
                    </button>
                </div>
            </header>

            <!-- CONTEÚDO SCROLLÁVEL -->
            <main class="flex-1 overflow-y-auto p-6 space-y-6" :class="{ 'p-3 space-y-3': telaCheia }">

                @if ($dashboard->eventTitle)
                    <div
                        class="bg-brand-50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-800 p-4 rounded-xl flex items-start gap-4 shadow-sm">
                        <div class="p-2 bg-brand-500 text-white rounded-lg shrink-0">
                            <i class="ri-information-line text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-brand-900 dark:text-brand-200">
                                {{ $dashboard->eventTitle }}</h4>
                            <p class="text-xs text-brand-700 dark:text-brand-400 mt-1">
                                {{ $dashboard->eventDescription }}</p>
                        </div>
                    </div>
                @endif

                <!-- CARDS DE RESUMO KPIs -->
                <div x-show="!telaCheia" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de Registros</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ number_format(method_exists($dados, 'total') ? $dados->total() : $dados->count()) }}
                            </h3>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl">
                            <i class="ri-list-check-2 text-2xl"></i>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de Colunas</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ count($colunas ?? []) }}
                            </h3>
                        </div>
                        <div
                            class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <i class="ri-layout-column-line text-2xl"></i>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Página Atual</p>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ method_exists($dados, 'currentPage') ? $dados->currentPage() : 1 }} /
                                {{ method_exists($dados, 'lastPage') ? $dados->lastPage() : 1 }}
                            </h3>
                        </div>
                        <div
                            class="p-3 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl">
                            <i class="ri-file-copy-2-line text-2xl"></i>
                        </div>
                    </div>
                </div>

                <!-- TABELA DE DADOS -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col"
                    :class="telaCheia ? 'h-full border-none rounded-none shadow-none' : ''">

                    <!-- Topo: Busca Global + Ações -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 space-y-3">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <form
                                action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                                method="GET" class="relative w-full sm:w-96">
                                @if (request('sort'))
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                                @endif
                                @if (!empty($colunaDuplicada))
                                    <input type="hidden" name="duplicate_column" value="{{ $colunaDuplicada }}">
                                @endif
                                @foreach ($filtrosColuna ?? [] as $col => $val)
                                    <input type="hidden" name="filters[{{ $col }}]"
                                        value="{{ $val }}">
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
                                <!-- Botão Detectar Duplicados -->
                                <button
                                    @click="painelDuplicadosOpen = !painelDuplicadosOpen; painelFiltrosOpen = false"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 flex items-center gap-2 transition"
                                    :class="{
                                        'bg-amber-500 text-white dark:bg-amber-600 dark:text-white': painelDuplicadosOpen ||
                                            {{ !empty($colunaDuplicada) ? 'true' : 'false' }}
                                    }">
                                    <i class="ri-file-copy-line text-amber-500"
                                        :class="{
                                            'text-white': painelDuplicadosOpen ||
                                                {{ !empty($colunaDuplicada) ? 'true' : 'false' }}
                                        }"></i>
                                    <span>Detectar Duplicados</span>
                                    @if (!empty($colunaDuplicada))
                                        <span
                                            class="bg-amber-700 text-white text-xs px-2 py-0.5 rounded-full font-bold">Ativo</span>
                                    @endif
                                </button>

                                <!-- Botão Filtros por Coluna -->
                                <button @click="painelFiltrosOpen = !painelFiltrosOpen; painelDuplicadosOpen = false"
                                    class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2 transition"
                                    :class="{ 'bg-brand-500 text-white dark:bg-brand-600 dark:text-white': painelFiltrosOpen }">
                                    <i class="ri-filter-3-line text-brand-500"
                                        :class="{ 'text-white': painelFiltrosOpen }"></i>
                                    <span>Filtros</span>
                                    @if (!empty($filtrosColuna))
                                        <span
                                            class="bg-brand-600 text-white text-xs px-2 py-0.5 rounded-full font-bold">
                                            {{ count($filtrosColuna) }}
                                        </span>
                                    @endif
                                </button>

                                <!-- Botão Tela Cheia -->
                                <button @click="telaCheia = !telaCheia"
                                    class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg border border-gray-300 dark:border-gray-700 transition flex items-center gap-1 text-sm font-medium"
                                    :title="telaCheia ? 'Sair da Tela Cheia' : 'Modo Tela Cheia'">
                                    <i :class="telaCheia ? 'ri-fullscreen-exit-line text-brand-500' : 'ri-fullscreen-line'"
                                        class="text-lg"></i>
                                    <span x-text="telaCheia ? 'Sair' : 'Expandir'" class="hidden md:inline"></span>
                                </button>
                            </div>
                        </div>

                        <!-- PAINEL DETECTAR DUPLICADOS -->
                        <div x-show="painelDuplicadosOpen" x-collapse x-cloak
                            class="pt-3 border-t border-gray-100 dark:border-gray-700/60">
                            <form
                                action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                                method="GET"
                                class="p-4 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-xl space-y-4">
                                @if (!empty($termoBusca))
                                    <input type="hidden" name="search" value="{{ $termoBusca }}">
                                @endif
                                @if (request('sort'))
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                                @endif
                                @foreach ($filtrosColuna ?? [] as $c => $v)
                                    <input type="hidden" name="filters[{{ $c }}]"
                                        value="{{ $v }}">
                                @endforeach

                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div
                                        class="text-xs font-semibold text-amber-800 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="ri-file-copy-line text-amber-500"></i> Buscar Registros com Valores
                                        Duplicados
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Exibe somente linhas cujo
                                        valor da coluna se repete</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <select name="duplicate_column"
                                        class="p-2 text-sm bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 rounded-lg text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-500">
                                        @foreach ($colunas as $col)
                                            <option value="{{ $col }}"
                                                {{ ($colunaDuplicada ?? '') === $col ? 'selected' : '' }}>
                                                Coluna: {{ $col }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit"
                                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition shadow-sm">
                                        <i class="ri-search-eye-line"></i> Localizar Duplicados
                                    </button>

                                    @if (!empty($colunaDuplicada))
                                        @php
                                            $clearDup = request()->query();
                                            unset($clearDup['duplicate_column']);
                                        @endphp
                                        <a href="{{ route('visualizar', array_merge(['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela], $clearDup)) }}"
                                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium flex items-center justify-center gap-2 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                            <i class="ri-close-circle-line text-red-500"></i> Limpar Filtro de
                                            Duplicados
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>

                        <!-- PAINEL FILTROS POR COLUNA -->
                        <div x-show="painelFiltrosOpen" x-collapse x-cloak
                            class="pt-3 border-t border-gray-100 dark:border-gray-700/60">
                            <form
                                action="{{ route('visualizar', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                                method="GET" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl space-y-4">
                                @if (!empty($termoBusca))
                                    <input type="hidden" name="search" value="{{ $termoBusca }}">
                                @endif
                                @if (request('sort'))
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                                @endif
                                @if (!empty($colunaDuplicada))
                                    <input type="hidden" name="duplicate_column" value="{{ $colunaDuplicada }}">
                                @endif

                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Adicionar
                                    Filtro por Coluna</div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3" x-data="{ colunaSel: '{{ !empty($filtrosColuna) ? array_keys($filtrosColuna)[0] : $colunas[0] ?? '' }}' }">
                                    <select x-model="colunaSel"
                                        class="p-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-800 dark:text-gray-200">
                                        @foreach ($colunas as $col)
                                            <option value="{{ $col }}">{{ $col }}</option>
                                        @endforeach
                                    </select>

                                    <input type="text" :name="'filters[' + colunaSel + ']'"
                                        placeholder="Digite o valor para filtrar..."
                                        class="p-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-brand-500 text-gray-800 dark:text-gray-200">

                                    <button type="submit"
                                        class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition shadow-sm">
                                        <i class="ri-add-line"></i> Aplicar Filtro
                                    </button>
                                </div>

                                @foreach ($filtrosColuna ?? [] as $c => $v)
                                    <input type="hidden" name="filters[{{ $c }}]"
                                        value="{{ $v }}">
                                @endforeach
                            </form>
                        </div>
                    </div>

                    <!-- TABELA PRINCIPAL DE DADOS -->
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-xs tracking-wider border-b border-gray-200 dark:border-gray-700 select-none sticky top-0 bg-white dark:bg-gray-800 z-10">
                                <tr>
                                    <th class="px-4 py-3 font-semibold text-center w-28">Ações</th>
                                    @foreach ($colunas as $coluna)
                                        @php
                                            $isSorted = ($sortColuna ?? '') === $coluna;
                                            $nextDirection =
                                                $isSorted && ($direction ?? 'asc') === 'asc' ? 'desc' : 'asc';
                                        @endphp
                                        <th
                                            class="px-6 py-3 font-semibold whitespace-nowrap hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer">
                                            <a href="{{ route('visualizar', array_merge(request()->query(), ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela, 'sort' => $coluna, 'direction' => $nextDirection])) }}"
                                                class="flex items-center gap-1.5 w-full h-full">
                                                <span>{{ $coluna }}</span>
                                                <i
                                                    class="{{ $isSorted ? (($direction ?? 'asc') === 'asc' ? 'ri-arrow-up-line text-brand-500 font-bold' : 'ri-arrow-down-line text-brand-500 font-bold') : 'ri-arrow-up-down-line text-gray-400 opacity-40 hover:opacity-100' }}"></i>
                                            </a>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($dados as $linha)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <!-- Ações por Linha -->
                                        <td class="px-4 py-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1">
                                                <!-- Ver Detalhes -->
                                                <button @click="registroSelecionado = {{ json_encode($linha) }}"
                                                    title="Ver Detalhes"
                                                    class="p-1.5 text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/40 rounded-lg transition">
                                                    <i class="ri-eye-line text-base"></i>
                                                </button>

                                                <!-- Editar Registro -->
                                                <button
                                                    @click="registroParaEditar = JSON.parse(JSON.stringify({{ json_encode($linha) }}))"
                                                    title="Editar Registro"
                                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                                    :class="{ 'opacity-40 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                                    class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/40 rounded-lg transition">
                                                    <i class="ri-pencil-line text-base"></i>
                                                </button>

                                                <!-- Excluir Registro -->
                                                <button @click="registroParaDeletar = {{ json_encode($linha) }}"
                                                    title="Excluir Registro"
                                                    :disabled="{{ is_null($dashboard->primaryKey) ? 'true' : 'false' }}"
                                                    :class="{ 'opacity-40 cursor-not-allowed': {{ is_null($dashboard->primaryKey) ? 'true' : 'false' }} }"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-lg transition">
                                                    <i class="ri-delete-bin-line text-base"></i>
                                                </button>
                                            </div>
                                        </td>

                                        @foreach ((array) $linha as $chaveCol => $valor)
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                                @if (is_null($valor))
                                                    <span class="text-xs text-gray-400 italic">null</span>
                                                @elseif(is_bool($valor))
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $valor ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                                        {{ $valor ? 'Verdadeiro' : 'Falso' }}
                                                    </span>
                                                @elseif(is_array($valor) || is_object($valor))
                                                    <span
                                                        class="font-mono text-xs bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded">{{ json_encode($valor) }}</span>
                                                @else
                                                    {{ $valor }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($colunas) > 0 ? count($colunas) + 1 : 1 }}"
                                            class="px-6 py-12 text-center text-gray-400">
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

            </main>
        </div>
    </div>

    <!-- MODAL EDITAR REGISTRO -->
    <div x-show="registroParaEditar !== null"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak
        x-transition>
        <div @click.away="registroParaEditar = null"
            class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form
                action="{{ route('visualizar.update', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                method="POST" class="flex flex-col h-full">
                @csrf
                @method('PUT')

                <!-- Passa a Chave Primária Detectada -->
                <input type="hidden" name="_primary_key_name"
                    value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
                <input type="hidden" name="_primary_key_value"
                    :value="registroParaEditar ? registroParaEditar['{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] :
                        ''">

                <div
                    class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center gap-2">
                        <i class="ri-pencil-line text-amber-500 text-xl"></i>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            Editar Registro - <span class="text-amber-500">{{ $tabela }}</span>
                        </h3>
                    </div>
                    <button type="button" @click="registroParaEditar = null"
                        class="text-gray-400 hover:text-gray-600 transition">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4 flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                        <template x-for="([coluna, valor]) in Object.entries(registroParaEditar)" :key="coluna">
                            <div class="space-y-1"
                                 :class="coluna === '{{ $dashboard->primaryKey }}' ? 'bg-amber-50 dark:bg-amber-900/20 p-2 rounded-lg border border-amber-100 dark:border-amber-800/50' : ''">

                                <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    x-text="coluna"></label>

                                <!-- Campo de Chave Primária (Read-only) -->
                                <div x-show="coluna === '{{ $dashboard->primaryKey }}'">
                                    <input type="text" :value="valor" disabled
                                        class="w-full p-2 text-sm bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg font-mono cursor-not-allowed"
                                        title="Chave Primária (Não editável)">
                                </div>

                                <!-- Campos Editáveis -->
                                <div x-show="coluna !== '{{ $dashboard->primaryKey }}'">
                                    <!-- Textarea para campos de texto longo -->
                                    <textarea x-show="coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao')"
                                        x-model="registroParaEditar[coluna]" rows="3"
                                        class="w-full p-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono outline-none transition"></textarea>

                                    <!-- Input simples para demais campos -->
                                    <input x-show="!(coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao'))"
                                        type="text" x-model="registroParaEditar[coluna]"
                                        class="w-full p-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono outline-none transition">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div
                    class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                    <button type="button" @click="registroParaEditar = null"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i class="ri-save-line"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CONFIRMAR EXCLUSÃO -->
    <div x-show="registroParaDeletar !== null"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak
        x-transition>
        <div @click.away="registroParaDeletar = null"
            class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4 text-center">
            <div
                class="p-3 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full w-12 h-12 flex items-center justify-center mx-auto text-2xl">
                <i class="ri-error-warning-line"></i>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirmar Exclusão</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Esta ação removerá permanentemente o registro da base de dados PostgreSQL. Deseja continuar?
                </p>
            </div>

            <form
                action="{{ route('visualizar.destroy', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
                method="POST" class="flex justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')

                <input type="hidden" name="_primary_key_name"
                    value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
                <input type="hidden" name="_primary_key_value"
                    :value="registroParaDeletar ? registroParaDeletar[
                        '{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] :
                        ''">

                <button type="button" @click="registroParaDeletar = null"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <i class="ri-delete-bin-line"></i> Excluir Registro
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL DETALHES DO REGISTRO -->
    <div x-show="registroSelecionado !== null"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak
        x-transition>
        <div @click.away="registroSelecionado = null"
            class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div
                class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2">
                    <i class="ri-file-text-line text-brand-500 text-xl"></i>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Detalhes do Registro - <span class="text-brand-500">{{ $tabela }}</span>
                    </h3>
                </div>
                <button @click="registroSelecionado = null"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-3 flex-1">
                <template x-for="(valor, chave) in registroSelecionado" :key="chave">
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-2 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 items-start">
                        <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 break-words"
                            x-text="chave"></span>
                        <div class="md:col-span-2 text-sm text-gray-800 dark:text-gray-200 font-mono break-all">
                            <template x-if="valor === null">
                                <span class="text-xs text-gray-400 italic">null</span>
                            </template>
                            <template x-if="typeof valor === 'boolean'">
                                <span
                                    :class="valor ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' :
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                    x-text="valor ? 'Verdadeiro' : 'Falso'"></span>
                            </template>
                            <template x-if="typeof valor === 'object' && valor !== null">
                                <pre class="text-xs p-2 bg-gray-200 dark:bg-gray-950 rounded text-green-600 dark:text-green-400 overflow-x-auto"
                                    x-text="JSON.stringify(valor, null, 2)"></pre>
                            </template>
                            <template x-if="valor !== null && typeof valor !== 'boolean' && typeof valor !== 'object'">
                                <span x-text="valor"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div
                class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                <div>
                    <template
                        x-if="registroSelecionado && (registroSelecionado.idevento || registroSelecionado.cpftrab || registroSelecionado.cpfbenef || registroSelecionado.cpf || registroSelecionado.matricula || registroSelecionado.num_cpf_trab || registroSelecionado.evento_id || registroSelecionado.id)">
                        <button
                            @click="carregarHistoricoTrabalhador(registroSelecionado.idevento || registroSelecionado.cpftrab || registroSelecionado.cpfbenef || registroSelecionado.cpf || registroSelecionado.matricula || registroSelecionado.num_cpf_trab || registroSelecionado.evento_id || registroSelecionado.id)"
                            class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
                            <i class="ri-time-line"></i> Ver Linha do Tempo
                        </button>
                    </template>
                </div>
                <button @click="registroSelecionado = null"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL LINHA DO TEMPO / HISTÓRICO DO TRABALHADOR -->
    <div x-show="modalHistoricoOpen"
        class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak
        x-transition>
        <div @click.away="modalHistoricoOpen = false"
            class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

            <div
                class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                        <i class="ri-time-line text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Linha do Tempo do Trabalhador
                            </h3>
                            <span x-show="historicoTrabalhador.length > 0"
                                class="text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300"
                                x-text="historicoTrabalhador.length + ' evento(s)'"></span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Histórico de envios e processamentos no
                            eSocial</p>
                    </div>
                </div>
                <button @click="modalHistoricoOpen = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <div x-show="carregandoHistorico" class="flex flex-col items-center justify-center py-16 space-y-4">
                    <div class="w-10 h-10 border-4 border-brand-500 border-t-transparent rounded-full animate-spin">
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Buscando histórico de eventos no
                        eSocial...</p>
                </div>

                <div x-show="!carregandoHistorico">
                    <template x-if="historicoTrabalhador.length === 0">
                        <div class="text-center py-16 px-4">
                            <div
                                class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="ri-history-line"></i>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Nenhum evento
                                histórico encontrado</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">Não foram
                                identificados registros de transmissão associados a este idevento/identificador.</p>
                        </div>
                    </template>

                    <template x-if="historicoTrabalhador.length > 0">
                        <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-4 space-y-6 pb-2">
                            <template x-for="(evento, index) in historicoTrabalhador"
                                :key="evento.historico_id || index">
                                <div class="relative pl-7 group">
                                    <div class="absolute -left-[17px] top-1.5 w-8 h-8 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold shadow-sm transition"
                                        :class="{
                                            'bg-emerald-500 text-white': evento.status_tipo === 'success',
                                            'bg-rose-500 text-white': evento.status_tipo === 'error',
                                            'bg-amber-500 text-white': evento.status_tipo === 'warning',
                                            'bg-gray-400 text-white': !evento.status_tipo || evento
                                                .status_tipo === 'default'
                                        }">
                                        <i :class="evento.status_icone || 'ri-circle-fill'"></i>
                                    </div>

                                    <div
                                        class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 rounded-xl p-4 shadow-sm hover:shadow transition">
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold font-mono uppercase"
                                                    :class="{
                                                        'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300': evento
                                                            .status_tipo === 'success',
                                                        'bg-rose-100 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300': evento
                                                            .status_tipo === 'error',
                                                        'bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300': evento
                                                            .status_tipo === 'warning',
                                                        'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200':
                                                            !evento.status_tipo || evento.status_tipo === 'default'
                                                    }"
                                                    x-text="evento.evento"></span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white"
                                                    x-text="evento.titulo || ('Evento ' + evento.evento)"></span>
                                            </div>
                                            <div
                                                class="flex items-center gap-1.5 text-xs font-mono text-gray-500 dark:text-gray-400 shrink-0">
                                                <i class="ri-calendar-event-line"></i>
                                                <span x-text="evento.data"></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 mb-2.5 flex-wrap">
                                            <span
                                                class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-0.5 rounded-full"
                                                :class="{
                                                    'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50': evento
                                                        .status_tipo === 'success',
                                                    'bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50': evento
                                                        .status_tipo === 'error',
                                                    'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50': evento
                                                        .status_tipo === 'warning',
                                                    'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700':
                                                        !evento.status_tipo || evento.status_tipo === 'default'
                                                }">
                                                <i :class="evento.status_icone" class="text-xs"></i>
                                                <span x-text="evento.status"></span>
                                            </span>

                                            <template x-if="evento.nr_recibo">
                                                <span
                                                    class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700 font-mono">
                                                    <i
                                                        class="ri-file-check-line text-emerald-600 dark:text-emerald-400"></i>
                                                    <span class="text-gray-400">Recibo:</span>
                                                    <span x-text="evento.nr_recibo"></span>
                                                </span>
                                            </template>

                                            <!-- Botão Editar dentro do Card da Linha do Tempo -->
                                            <template x-if="evento.dados_evento">
                                                <button
                                                    @click="registroParaEditar = JSON.parse(JSON.stringify(evento.dados_evento)); modalHistoricoOpen = false;"
                                                    title="Editar Registro Original"
                                                    class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 hover:bg-amber-500 text-amber-600 dark:text-amber-400 hover:text-white rounded-md border border-amber-500/30 transition flex items-center gap-1.5 ml-auto">
                                                    <i class="ri-pencil-line"></i>
                                                    <span>Editar Registro</span>
                                                </button>
                                            </template>

                                            <template x-if="evento.protocolo">
                                                <span
                                                    class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700 font-mono">
                                                    <i class="ri-hashtag text-brand-500"></i>
                                                    <span class="text-gray-400">Prot:</span>
                                                    <span x-text="evento.protocolo"></span>
                                                </span>
                                            </template>
                                        </div>

                                        <template x-if="evento.message">
                                            <div class="mt-2 text-xs bg-white dark:bg-gray-800/80 p-2.5 rounded-lg border border-gray-200/80 dark:border-gray-700/60 font-mono text-gray-600 dark:text-gray-300 whitespace-pre-wrap break-all leading-relaxed"
                                                x-text="evento.message"></div>
                                        </template>

                                        <!-- Renderiza Dados Cadastrais Retornados da Tabela do Evento -->
                                        <template x-if="evento.dados_evento">
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                                <p class="text-[11px] font-bold uppercase text-gray-400 mb-1.5">
                                                    Dados do Registro (<span x-text="evento.evento"></span>)
                                                </p>
                                                <div
                                                    class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-white dark:bg-gray-950 p-2.5 rounded-lg border border-gray-200 dark:border-gray-800 text-xs font-mono">
                                                    <template x-for="(v, k) in evento.dados_evento"
                                                        :key="k">
                                                        <div class="truncate">
                                                            <span class="text-gray-400" x-text="k + ':'"></span>
                                                            <span
                                                                class="text-gray-800 dark:text-gray-200 font-semibold"
                                                                x-text="v !== null ? v : 'null'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div
                class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                <button @click="modalHistoricoOpen = false"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Fechar
                </button>
            </div>
        </div>
    </div>

</body>

</html>
