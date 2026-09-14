<header x-show="!telaCheia"
    class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-10">
    <div class="flex items-center gap-2 text-xs">
        @if (request()->routeIs('certificados.*'))
            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold rounded-md font-mono border border-amber-200/60 dark:border-amber-800/40">
                <i class="ri-shield-keyhole-line text-amber-500"></i>
                <span>Gestão de Certificados</span>
            </div>
            <span class="text-gray-400">/</span>
            <span class="text-gray-500 dark:text-gray-400 font-medium">Assinatura Digital</span>
        @else
            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 dark:bg-gray-900 rounded-md text-gray-500 dark:text-gray-400 font-mono">
                <i class="ri-database-2-line text-brand-500"></i>
                <span>{{ $schema ?? 'public' }}</span>
            </div>
            <span class="text-gray-400">/</span>
            <div class="px-2.5 py-1 bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 font-bold rounded-md font-mono">
                {{ $tabela ?? 'Tabela' }}
            </div>
        @endif
    </div>

    <div class="flex items-center gap-3">
        <!-- Alternar entre Visualizador de Tabelas e Certificados -->
        <div class="flex items-center gap-3">
            <a href="{{ request()->routeIs('certificados.*') ? route('visualizar', ['banco' => $banco]) : route('certificados.index', ['banco' => $banco]) }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border transition {{ request()->routeIs('certificados.*') ? 'bg-brand-50 border-brand-200 text-brand-700 dark:bg-brand-950/40 dark:border-brand-800 dark:text-brand-300 hover:bg-brand-100' : 'bg-amber-50/70 border-amber-200/80 text-amber-700 dark:bg-amber-950/30 dark:border-amber-800/50 dark:text-amber-300 hover:bg-amber-100/80' }}">
                <i class="{{ request()->routeIs('certificados.*') ? 'ri-table-line text-brand-500' : 'ri-shield-keyhole-line text-amber-500' }} text-sm"></i>
                <span>{{ request()->routeIs('certificados.*') ? 'Ver Tabelas' : 'Certificado Digital' }}</span>
            </a>

            <a href="{{ route('xml.import.view', ['banco' => $banco]) }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-brand-600 bg-brand-500 hover:bg-brand-600 text-white transition shadow-sm">
                <i class="ri-upload-cloud-2-line text-sm"></i>
                <span>Importar XML</span>
            </a>
        </div>

        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-2.5 py-1">
            <i class="ri-server-line text-gray-400 text-xs"></i>
            <select onchange="location = this.value;"
                class="bg-transparent text-gray-800 dark:text-gray-200 text-xs focus:outline-none cursor-pointer">
                @foreach ($bancosDisponiveis as $key => $nome)
                    <option value="{{ request()->routeIs('certificados.*') ? route('certificados.index', ['banco' => $key]) : route('visualizar', ['banco' => $key]) }}"
                        {{ $banco === $key ? 'selected' : '' }}>
                        {{ $nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <button @click="toggleDarkMode()"
            class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
            title="Alternar Tema">
            <i :class="darkMode ? 'ri-sun-line text-amber-400' : 'ri-moon-line text-gray-600'" class="text-lg"></i>
        </button>
    </div>
</header>