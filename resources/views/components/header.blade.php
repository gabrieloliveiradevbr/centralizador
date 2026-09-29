<header x-show="!telaCheia"
    class="h-14 bg-white dark:bg-slate-900 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between px-5 z-10">
    
    <!-- Breadcrumb Minimalista -->
    <div class="flex items-center gap-2 text-xs">
        @if (request()->routeIs('certificados.*'))
            <span class="text-slate-400 dark:text-slate-500">Módulos</span>
            <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 text-xs"></i>
            <span class="font-medium text-slate-900 dark:text-white">Gestão de Certificados</span>
        @else
            <span class="text-slate-400 dark:text-slate-500 font-mono">{{ $schema ?? 'public' }}</span>
            <i class="ri-arrow-right-s-line text-slate-300 dark:text-slate-600 text-xs"></i>
            <span class="font-medium text-slate-900 dark:text-white font-mono">{{ $tabela ?? 'Tabela' }}</span>
        @endif
    </div>

    <!-- Ações e Navegação -->
    <div class="flex items-center gap-2.5">
        <!-- Navegação entre Tabelas / Certificados -->
        <a href="{{ request()->routeIs('certificados.*') ? route('visualizar', ['banco' => $banco]) : route('certificados.index', ['banco' => $banco]) }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
            <i class="{{ request()->routeIs('certificados.*') ? 'ri-table-line' : 'ri-shield-keyhole-line' }} text-slate-400"></i>
            <span>{{ request()->routeIs('certificados.*') ? 'Visualizar Tabelas' : 'Certificados' }}</span>
        </a>

        <!-- Botão Importar XML -->
        <a href="{{ route('xml.import.view', ['banco' => $banco]) }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 transition shadow-sm">
            <i class="ri-upload-cloud-2-line text-xs"></i>
            <span>Importar XML</span>
        </a>

        <div class="h-4 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

        <!-- Seletor de Banco/Conexão -->
        <div class="relative flex items-center bg-slate-100/70 dark:bg-slate-800/80 rounded-lg px-2.5 py-1 text-xs">
            <i class="ri-server-line text-slate-400 mr-2 text-xs"></i>
            <select onchange="location = this.value;"
                class="bg-transparent text-slate-700 dark:text-slate-300 text-xs focus:outline-none cursor-pointer pr-1 font-medium">
                @foreach ($bancosDisponiveis as $key => $nome)
                    <option value="{{ request()->routeIs('certificados.*') ? route('certificados.index', ['banco' => $key]) : route('visualizar', ['banco' => $key]) }}"
                        {{ $banco === $key ? 'selected' : '' }}>
                        {{ $nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Alternar Tema -->
        <button @click="toggleDarkMode()"
            class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition"
            title="Alternar Tema">
            <i :class="darkMode ? 'ri-sun-line text-amber-400' : 'ri-moon-line'" class="text-base"></i>
        </button>
    </div>
</header>