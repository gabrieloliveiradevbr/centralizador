<header x-show="!telaCheia"
    class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-10">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <i class="ri-database-2-line"></i>
        <span>Schema: <strong class="text-gray-800 dark:text-gray-200 font-medium">{{ $schema ?? 'public' }}</strong></span>
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