<aside
    x-show="!telaCheia"
    :class="sidebarOpen ? 'w-72' : 'w-[68px]'"
    class="bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800
           transition-all duration-300 flex flex-col z-20 overflow-hidden shadow-sm">

    {{-- HEADER --}}
    <div class="h-16 min-h-[64px] flex items-center border-b border-gray-200 dark:border-gray-800">

        <div
            class="flex items-center gap-3 px-4 flex-1 min-w-0"
            x-show="sidebarOpen"
            x-transition.opacity>

            <div class="relative shrink-0">
                <div class="w-9 h-9 rounded-xl bg-brand-500 text-white
                            flex items-center justify-center
                            shadow-md shadow-brand-500/20">
                    <i class="ri-database-2-line text-lg"></i>
                </div>

                <span
                    class="absolute -right-0.5 -bottom-0.5 w-2.5 h-2.5
                           rounded-full bg-emerald-500 border-2
                           border-white dark:border-gray-900">
                </span>
            </div>

            <div class="flex flex-col min-w-0">
                <span class="font-bold text-sm text-gray-900 dark:text-white truncate">
                    Centralizador
                </span>

                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-mono">
                    eSocial • v1.0
                </span>
            </div>
        </div>

        {{-- Ícone quando fechada --}}
        <div
            x-show="!sidebarOpen"
            x-transition.opacity
            class="flex-1 flex justify-center">

            <div
                class="w-9 h-9 rounded-xl bg-brand-500 text-white
                       flex items-center justify-center
                       shadow-md shadow-brand-500/20">
                <i class="ri-database-2-line text-lg"></i>
            </div>
        </div>

        {{-- BOTÃO --}}
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="mr-3 p-2 rounded-lg
                   text-gray-400 hover:text-gray-700
                   dark:text-gray-500 dark:hover:text-gray-200
                   hover:bg-gray-100 dark:hover:bg-gray-800
                   transition-all duration-200 shrink-0"
            :title="sidebarOpen ? 'Recolher menu' : 'Expandir menu'">

            <i
                :class="sidebarOpen
                    ? 'ri-layout-left-line'
                    : 'ri-layout-right-line'"
                class="text-lg">
            </i>
        </button>
    </div>


    {{-- NAVEGAÇÃO --}}
    <nav
        class="flex-1 overflow-y-auto px-2.5 py-3 space-y-1
               scrollbar-thin scrollbar-thumb-gray-300
               dark:scrollbar-thumb-gray-700"
        x-data="{ buscaTabelaSidebar: '' }">

        {{-- BUSCA --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="px-1 mb-4">

            <div class="relative group">

                <i class="ri-search-2-line
                          absolute left-3 top-1/2 -translate-y-1/2
                          text-gray-400 text-sm
                          group-focus-within:text-brand-500 transition">
                </i>

                <input
                    type="text"
                    x-model="buscaTabelaSidebar"
                    placeholder="Buscar tabela..."
                    @keydown.escape="buscaTabelaSidebar = ''"
                    class="w-full h-9 pl-9 pr-8
                           text-xs
                           bg-gray-50 dark:bg-gray-800
                           border border-gray-200 dark:border-gray-700
                           rounded-xl
                           text-gray-700 dark:text-gray-200
                           placeholder:text-gray-400
                           focus:outline-none
                           focus:border-brand-500
                           focus:ring-2 focus:ring-brand-500/10
                           transition">

                <button
                    type="button"
                    x-show="buscaTabelaSidebar"
                    x-transition
                    @click="buscaTabelaSidebar = ''"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2
                           text-gray-400 hover:text-gray-600
                           dark:hover:text-gray-200">

                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>
        </div>

        {{-- LINK GESTÃO DE CERTIFICADOS --}}
        <div class="px-1 mb-3">
            <a href="{{ route('certificados.index', ['banco' => $banco]) }}"
               class="group w-full flex items-center gap-2 px-2.5 py-2 rounded-xl text-left transition-all duration-200 {{ request()->routeIs('certificados.*') ? 'bg-amber-500 text-white font-semibold shadow-sm shadow-amber-500/20' : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300' }}"
               :title="sidebarOpen ? '' : 'Certificado Digital'">
                <span class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center {{ request()->routeIs('certificados.*') ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400' }}">
                    <i class="ri-shield-keyhole-line text-sm"></i>
                </span>
                <span x-show="sidebarOpen" class="flex-1 min-w-0 text-xs font-semibold truncate">
                    Certificado Digital
                </span>
                <span x-show="sidebarOpen" class="px-1.5 py-0.5 rounded-md text-[9px] font-mono {{ request()->routeIs('certificados.*') ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                    A1
                </span>
            </a>
        </div>

        {{-- ============================= --}}
        {{-- FASES DO ESOCIAL --}}
        {{-- ============================= --}}

        @if (!empty($menuFasesEsocial))

            {{-- TÍTULO --}}
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                class="px-2 mb-2">

                <div class="flex items-center justify-between">

                    <span class="flex items-center gap-2
                                 text-[10px] font-bold uppercase
                                 tracking-widest
                                 text-gray-400 dark:text-gray-500">

                        <i class="ri-shield-check-line text-brand-500"></i>

                        eSocial
                    </span>

                    <span
                        class="px-1.5 py-0.5 rounded-md
                               text-[9px] font-bold font-mono
                               bg-brand-50 text-brand-600
                               dark:bg-brand-950/40 dark:text-brand-400
                               border border-brand-100 dark:border-brand-900">

                        MOS
                    </span>
                </div>
            </div>


            @foreach ($menuFasesEsocial as $faseNome => $faseInfo)

                @php
                    $tabelasFase = $faseInfo['tabelas'] ?? [];
                    $icone = $faseInfo['icone'] ?? 'ri-folder-line';

                    $hasActiveTable = collect($tabelasFase)
                        ->contains(fn($i) => $i->table_name === $tabela);

                    $isS3000 = str_contains($faseNome, 'S-3000') ||
                               str_contains($faseNome, 'Exclusão');

                    $isSST = str_contains($faseNome, 'SST');

                    $isTabela = str_contains($faseNome, 'Tabela');

                    $isNaoPeriodico = str_contains($faseNome, 'Não Periódico');

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

                            const termo = buscaTabelaSidebar
                                .toLowerCase()
                                .trim();

                            return this.tabelas.some(t =>
                                t.includes(termo)
                            );
                        }
                    }"

                    x-show="temMatch"
                    x-transition
                    class="mb-1">


                    {{-- FASE --}}
                    <button
                        @click="open = !open"
                        class="group w-full flex items-center
                               gap-2 px-2.5 py-2
                               rounded-xl
                               text-left
                               transition-all duration-200

                               hover:bg-gray-100
                               dark:hover:bg-gray-800

                               {{ $hasActiveTable
                                   ? 'bg-gray-50 dark:bg-gray-800/70'
                                   : '' }}">

                        {{-- ÍCONE --}}
                        <span
                            class="w-7 h-7 shrink-0
                                   rounded-lg
                                   flex items-center justify-center

                                   {{ $isS3000
                                       ? 'bg-red-50 text-red-500 dark:bg-red-950/30 dark:text-red-400'
                                       : ($isSST
                                           ? 'bg-emerald-50 text-emerald-500 dark:bg-emerald-950/30 dark:text-emerald-400'
                                           : ($isTabela
                                               ? 'bg-blue-50 text-blue-500 dark:bg-blue-950/30 dark:text-blue-400'
                                               : ($isNaoPeriodico
                                                   ? 'bg-indigo-50 text-indigo-500 dark:bg-indigo-950/30 dark:text-indigo-400'
                                                   : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                               )
                                           )
                                       )
                                   }}">

                            <i class="{{ $icone }} text-sm"></i>
                        </span>


                        {{-- NOME --}}
                        <span
                            x-show="sidebarOpen"
                            class="flex-1 min-w-0
                                   text-xs font-semibold
                                   text-gray-700 dark:text-gray-300
                                   truncate">

                            {{ $faseNome }}
                        </span>


                        {{-- CONTADOR --}}
                        <span
                            x-show="sidebarOpen"
                            class="px-1.5 py-0.5 rounded-md
                                   bg-gray-100 dark:bg-gray-800
                                   text-[9px] font-mono
                                   text-gray-500 dark:text-gray-400
                                   shrink-0">

                            {{ count($tabelasFase) }}
                        </span>


                        {{-- SETA --}}
                        <i
                            x-show="sidebarOpen"
                            :class="
                                (open || buscaTabelaSidebar.trim() !== '')
                                    ? 'ri-arrow-down-s-line'
                                    : 'ri-arrow-right-s-line'
                            "
                            class="text-gray-400 text-sm
                                   transition-transform">
                        </i>

                    </button>


                    {{-- TABELAS --}}
                    <div
                        x-show="(open || buscaTabelaSidebar.trim() !== '') && sidebarOpen"
                        x-collapse
                        class="relative mt-1 ml-[23px] pl-3
                               border-l border-gray-200
                               dark:border-gray-800 space-y-0.5">

                        @foreach ($tabelasFase as $item)

                            <a
                                href="{{ route('visualizar', [
                                    'banco' => $banco,
                                    'schema' => 'esocial',
                                    'tabela' => $item->table_name
                                ]) }}"

                                x-show="
                                    !buscaTabelaSidebar ||
                                    '{{ strtolower($item->table_name) }}'
                                    .includes(
                                        buscaTabelaSidebar
                                        .toLowerCase()
                                        .trim()
                                    )
                                "

                                class="group relative flex items-center
                                       px-2.5 py-1.5
                                       rounded-lg
                                       text-[11px]
                                       font-mono
                                       truncate
                                       transition-all duration-150

                                       {{ ($tabela ?? null) === $item->table_name
                                           ? 'bg-brand-500 text-white font-semibold shadow-sm shadow-brand-500/20'
                                           : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200 hover:translate-x-0.5'
                                       }}">

                                {{-- INDICADOR --}}
                                @if (($tabela ?? null) === $item->table_name)

                                    <span
                                        class="absolute -left-[17px]
                                               top-1/2 -translate-y-1/2
                                               w-1.5 h-1.5
                                               rounded-full
                                               bg-brand-500
                                               ring-4 ring-white
                                               dark:ring-gray-900">
                                    </span>

                                @endif

                                <span class="truncate">
                                    {{ $item->table_name }}
                                </span>

                            </a>

                        @endforeach
                    </div>

                </div>

            @endforeach


        {{-- ============================= --}}
        {{-- SCHEMAS NORMAIS --}}
        {{-- ============================= --}}

        @elseif(!empty($menuSchemas))

            @foreach ($menuSchemas as $nomeSchema => $tabelas)

                @php
                    $nomesSchemaJson = json_encode(
                        $tabelas
                            ->pluck('table_name')
                            ->map(fn($t) => strtolower($t))
                            ->values()
                            ->all()
                    );
                @endphp


                <div
                    x-data="{
                        open: {{ ($schema ?? null) === $nomeSchema ? 'true' : 'false' }},
                        tabelas: {{ $nomesSchemaJson }},

                        get temMatch() {
                            if (!buscaTabelaSidebar.trim()) return true;

                            const termo = buscaTabelaSidebar
                                .toLowerCase()
                                .trim();

                            return this.tabelas.some(t =>
                                t.includes(termo)
                            );
                        }
                    }"

                    x-show="temMatch"
                    x-transition
                    class="mb-1">


                    {{-- SCHEMA --}}
                    <button
                        @click="open = !open"
                        class="group w-full flex items-center
                               gap-2 px-2.5 py-2
                               rounded-xl
                               hover:bg-gray-100
                               dark:hover:bg-gray-800
                               transition">

                        <span
                            class="w-7 h-7 shrink-0
                                   rounded-lg
                                   bg-brand-50
                                   dark:bg-brand-950/30
                                   text-brand-500
                                   flex items-center justify-center">

                            <i class="ri-folder-3-line text-sm"></i>
                        </span>


                        <span
                            x-show="sidebarOpen"
                            class="flex-1 text-left
                                   text-xs font-semibold
                                   text-gray-600 dark:text-gray-300
                                   truncate">

                            {{ strtoupper($nomeSchema) }}
                        </span>


                        <span
                            x-show="sidebarOpen"
                            class="px-1.5 py-0.5 rounded-md
                                   bg-gray-100 dark:bg-gray-800
                                   text-[9px] font-mono
                                   text-gray-500 dark:text-gray-400">

                            {{ $tabelas->count() }}
                        </span>


                        <i
                            x-show="sidebarOpen"
                            :class="
                                (open || buscaTabelaSidebar.trim() !== '')
                                    ? 'ri-arrow-down-s-line'
                                    : 'ri-arrow-right-s-line'
                            "
                            class="text-gray-400 text-sm">
                        </i>

                    </button>


                    {{-- TABELAS --}}
                    <div
                        x-show="(open || buscaTabelaSidebar.trim() !== '') && sidebarOpen"
                        x-collapse
                        class="mt-1 ml-[23px] pl-3
                               border-l border-gray-200
                               dark:border-gray-800 space-y-0.5">

                        @foreach ($tabelas as $item)

                            <a
                                href="{{ route('visualizar', [
                                    'banco' => $banco,
                                    'schema' => $nomeSchema,
                                    'tabela' => $item->table_name
                                ]) }}"

                                x-show="
                                    !buscaTabelaSidebar ||
                                    '{{ strtolower($item->table_name) }}'
                                    .includes(
                                        buscaTabelaSidebar
                                        .toLowerCase()
                                        .trim()
                                    )
                                "

                                class="block px-2.5 py-1.5
                                       rounded-lg
                                       text-[11px]
                                       font-mono
                                       truncate
                                       transition-all duration-150

                                       {{ ($tabela ?? null) === $item->table_name &&
                                          ($schema ?? null) === $nomeSchema
                                           ? 'bg-brand-500 text-white font-semibold shadow-sm'
                                           : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200 hover:translate-x-0.5'
                                       }}">

                                {{ $item->table_name }}

                            </a>

                        @endforeach
                    </div>

                </div>

            @endforeach

        @endif


        {{-- RESULTADO DA BUSCA --}}
        <div
            x-show="buscaTabelaSidebar"
            class="px-3 py-8 text-center">

            <i class="ri-search-eye-line
                      text-2xl text-gray-300
                      dark:text-gray-600">
            </i>

            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                Procure por uma tabela
            </p>

        </div>

    </nav>


    {{-- FOOTER --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        class="px-3 py-3
               border-t border-gray-200
               dark:border-gray-800">

        <div
            class="flex items-center gap-2 px-2 py-1.5
                   rounded-lg
                   bg-gray-50 dark:bg-gray-800/60">

            <span
                class="w-2 h-2 rounded-full
                       bg-emerald-500">
            </span>

            <span
                class="text-[10px] text-gray-500
                       dark:text-gray-400">

                Banco conectado
            </span>

        </div>

    </div>

</aside>
