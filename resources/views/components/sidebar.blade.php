<aside x-show="!telaCheia" :class="sidebarOpen ? 'w-64' : 'w-20'"
    class="bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 flex flex-col z-20">

    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3 overflow-hidden" x-show="sidebarOpen">
            <div class="p-2 bg-brand-500 text-white rounded-lg">
                <i class="ri-database-2-line text-xl"></i>
            </div>
            <span class="font-bold text-lg text-gray-900 dark:text-white truncate">Centralizador</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
            <i :class="sidebarOpen ? 'ri-indent-decrease' : 'ri-indent-increase'" class="text-xl"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-3" x-data="{ buscaTabelaSidebar: '' }">
        @if (!empty($menuFasesEsocial))
            <div x-show="sidebarOpen" class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 border-b border-gray-200 dark:border-gray-700 mb-2 flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <i class="ri-shield-check-line text-brand-500"></i> Eventos eSocial
                </span>
                <span class="bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 px-1.5 py-0.5 rounded font-mono text-[10px]">MOS</span>
            </div>

            <div x-show="sidebarOpen" class="mb-3 px-1">
                <div class="relative">
                    <i class="ri-search-2-line absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" x-model="buscaTabelaSidebar" placeholder="Filtrar eventos (ex: 2200)..."
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
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-white/80 dark:bg-gray-900/60 font-mono font-normal">({{ count($tabelasFase) }})</span>
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
                            <span x-show="sidebarOpen">{{ strtoupper($nomeSchema) }} ({{ $tabelas->count() }})</span>
                        </span>
                        <i x-show="sidebarOpen" :class="open ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'"></i>
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