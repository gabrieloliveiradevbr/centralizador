<aside
    x-show="!telaCheia"
    :class="sidebarOpen ? 'w-64' : 'w-[60px]'"
    class="bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800
           transition-all duration-200 flex flex-col z-20 overflow-hidden select-none">

    {{-- HEADER DA SIDEBAR --}}
    <div class="h-14 min-h-[56px] flex items-center justify-between px-3.5 border-b border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center gap-2.5 min-w-0" x-show="sidebarOpen" x-transition.opacity>
            <div class="w-7 h-7 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center font-bold text-xs shrink-0">
                <i class="ri-database-2-line"></i>
            </div>
            <div class="flex flex-col min-w-0">
                <span class="font-semibold text-xs text-slate-900 dark:text-white truncate">
                    Centralizador
                </span>
                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">
                    eSocial
                </span>
            </div>
        </div>

        <div x-show="!sidebarOpen" x-transition.opacity class="mx-auto">
            <div class="w-7 h-7 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center font-bold text-xs">
                <i class="ri-database-2-line"></i>
            </div>
        </div>

        <button
            @click="sidebarOpen = !sidebarOpen"
            class="p-1 rounded-md text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            :title="sidebarOpen ? 'Recolher menu' : 'Expandir menu'">
            <i :class="sidebarOpen ? 'ri-side-bar-line' : 'ri-layout-left-line'" class="text-base"></i>
        </button>
    </div>

    {{-- NAVEGAÇÃO --}}
    <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1" x-data="{ buscaTabelaSidebar: '' }">

        {{-- CAMPO DE BUSCA --}}
        <div x-show="sidebarOpen" x-transition.opacity class="px-1.5 mb-2.5">
            <div class="relative">
                <i class="ri-search-line absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input
                    type="text"
                    x-model="buscaTabelaSidebar"
                    placeholder="Filtrar eventos..."
                    @keydown.escape="buscaTabelaSidebar = ''"
                    class="w-full h-8 pl-7 pr-7 text-xs bg-slate-100/70 dark:bg-slate-800/60 border border-transparent focus:border-slate-300 dark:focus:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none transition">
                <button
                    type="button"
                    x-show="buscaTabelaSidebar"
                    @click="buscaTabelaSidebar = ''"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="ri-close-line text-sm"></i>
                </button>
            </div>
        </div>

        {{-- LINK GESTÃO DE CERTIFICADOS --}}
        <div class="px-1 mb-2">
            <a href="{{ route('certificados.index', ['banco' => $banco]) }}"
               class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs transition {{ request()->routeIs('certificados.*') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/60 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
               :title="sidebarOpen ? '' : 'Certificado Digital'">
                <i class="ri-shield-keyhole-line text-slate-400 text-sm shrink-0"></i>
                <span x-show="sidebarOpen" class="flex-1 truncate">Certificado Digital</span>
                <span x-show="sidebarOpen" class="text-[9px] font-mono text-slate-400">A1</span>
            </a>
        </div>

        {{-- SEÇÃO ESOCIAL --}}
        @if (!empty($menuFasesEsocial))
            <div x-show="sidebarOpen" x-transition.opacity class="px-2 pt-2 pb-1">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Eventos eSocial
                </span>
            </div>

            @foreach ($menuFasesEsocial as $faseNome => $faseInfo)
                @php
                    $tabelasFase = $faseInfo['tabelas'] ?? [];
                    $icone = $faseInfo['icone'] ?? 'ri-folder-line';

                    $hasActiveTable = collect($tabelasFase)->contains(fn($i) => $i->table_name === $tabela);

                    $nomesTabelasJson = json_encode(
                        array_values(
                            array_map(
                                fn($i) => strtolower($i->table_name),
                                (array) $tabelasFase
                            )
                        )
                    );
                @endphp

                <div
                    x-data="{
                        open: {{ $hasActiveTable ? 'true' : 'false' }},
                        tabelas: {{ $nomesTabelasJson }},
                        get temMatch() {
                            if (!buscaTabelaSidebar.trim()) return true;
                            const termo = buscaTabelaSidebar.toLowerCase().trim();
                            return this.tabelas.some(t => t.includes(termo));
                        }
                    }"
                    x-show="temMatch"
                    class="mb-0.5">

                    {{-- BOTÃO DA FASE --}}
                    <button
                        @click="open = !open"
                        class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-xs transition {{ $hasActiveTable ? 'text-slate-900 dark:text-white font-medium bg-slate-50 dark:bg-slate-800/40' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/60 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">

                        <i class="{{ $icone }} text-slate-400 text-sm shrink-0"></i>

                        <span x-show="sidebarOpen" class="flex-1 truncate text-xs">
                            {{ $faseNome }}
                        </span>

                        <span x-show="sidebarOpen" class="text-[10px] font-mono text-slate-400 dark:text-slate-500 shrink-0">
                            {{ count($tabelasFase) }}
                        </span>

                        <i x-show="sidebarOpen"
                           :class="(open || buscaTabelaSidebar.trim() !== '') ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'"
                           class="text-slate-400 text-xs transition-transform"></i>
                    </button>

                    {{-- LISTA DE TABELAS --}}
                    <div
                        x-show="(open || buscaTabelaSidebar.trim() !== '') && sidebarOpen"
                        x-collapse
                        class="mt-0.5 ml-4 pl-2 border-l border-slate-200 dark:border-slate-800 space-y-0.5">

                        @foreach ($tabelasFase as $item)
                            <a
                                href="{{ route('visualizar', [
                                    'banco' => $banco,
                                    'schema' => 'esocial',
                                    'tabela' => $item->table_name
                                ]) }}"
                                x-show="!buscaTabelaSidebar || '{{ strtolower($item->table_name) }}'.includes(buscaTabelaSidebar.toLowerCase().trim())"
                                class="block px-2 py-1 rounded-md text-[11px] font-mono truncate transition {{ ($tabela ?? null) === $item->table_name ? 'bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 font-medium' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100/60 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                                {{ $item->table_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

        @elseif(!empty($menuSchemas))
            @foreach ($menuSchemas as $nomeSchema => $tabelas)
                @php
                    $nomesSchemaJson = json_encode(
                        $tabelas->pluck('table_name')->map(fn($t) => strtolower($t))->values()->all()
                    );
                @endphp

                <div
                    x-data="{
                        open: {{ ($schema ?? null) === $nomeSchema ? 'true' : 'false' }},
                        tabelas: {{ $nomesSchemaJson }},
                        get temMatch() {
                            if (!buscaTabelaSidebar.trim()) return true;
                            const termo = buscaTabelaSidebar.toLowerCase().trim();
                            return this.tabelas.some(t => t.includes(termo));
                        }
                    }"
                    x-show="temMatch"
                    class="mb-0.5">

                    <button
                        @click="open = !open"
                        class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-left text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100/60 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition">
                        <i class="ri-folder-line text-slate-400 text-sm shrink-0"></i>
                        <span x-show="sidebarOpen" class="flex-1 truncate uppercase text-xs">
                            {{ $nomeSchema }}
                        </span>
                        <span x-show="sidebarOpen" class="text-[10px] font-mono text-slate-400">
                            {{ $tabelas->count() }}
                        </span>
                        <i x-show="sidebarOpen"
                           :class="(open || buscaTabelaSidebar.trim() !== '') ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'"
                           class="text-slate-400 text-xs"></i>
                    </button>

                    <div
                        x-show="(open || buscaTabelaSidebar.trim() !== '') && sidebarOpen"
                        x-collapse
                        class="mt-0.5 ml-4 pl-2 border-l border-slate-200 dark:border-slate-800 space-y-0.5">
                        @foreach ($tabelas as $item)
                            <a
                                href="{{ route('visualizar', [
                                    'banco' => $banco,
                                    'schema' => $nomeSchema,
                                    'tabela' => $item->table_name
                                ]) }}"
                                x-show="!buscaTabelaSidebar || '{{ strtolower($item->table_name) }}'.includes(buscaTabelaSidebar.toLowerCase().trim())"
                                class="block px-2 py-1 rounded-md text-[11px] font-mono truncate transition {{ ($tabela ?? null) === $item->table_name && ($schema ?? null) === $nomeSchema ? 'bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 font-medium' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100/60 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                                {{ $item->table_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

    </nav>

    {{-- FOOTER DA SIDEBAR --}}
    <div x-show="sidebarOpen" x-transition.opacity class="px-3 py-2.5 border-t border-slate-200/80 dark:border-slate-800">
        <div class="flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span class="truncate font-mono">{{ $banco }}</span>
        </div>
    </div>
</aside>
