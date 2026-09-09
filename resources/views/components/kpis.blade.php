<div x-show="!telaCheia" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm flex items-center justify-between hover:border-brand-300 dark:hover:border-brand-700 transition">
        <div class="space-y-0.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total de Registros</p>
            <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ number_format(method_exists($dados, 'total') ? $dados->total() : $dados->count()) }}
            </h3>
        </div>
        <div class="w-11 h-11 bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 rounded-xl flex items-center justify-center text-xl shadow-sm">
            <i class="ri-database-2-line"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm flex items-center justify-between hover:border-indigo-300 dark:hover:border-indigo-700 transition">
        <div class="space-y-0.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total de Colunas</p>
            <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ count($colunas ?? []) }}
            </h3>
        </div>
        <div class="w-11 h-11 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center text-xl shadow-sm">
            <i class="ri-table-line"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200/80 dark:border-gray-700/80 shadow-sm flex items-center justify-between sm:col-span-2 lg:col-span-1 hover:border-purple-300 dark:hover:border-purple-700 transition">
        <div class="space-y-0.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Página Atual</p>
            <h3 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ method_exists($dados, 'currentPage') ? $dados->currentPage() : 1 }}
                <span class="text-sm font-medium text-gray-400">/ {{ method_exists($dados, 'lastPage') ? $dados->lastPage() : 1 }}</span>
            </h3>
        </div>
        <div class="w-11 h-11 bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center text-xl shadow-sm">
            <i class="ri-pages-line"></i>
        </div>
    </div>
</div>