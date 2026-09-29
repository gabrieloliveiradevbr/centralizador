<div x-show="!telaCheia" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
    <!-- Total de Registros -->
    <div class="bg-white dark:bg-slate-900 px-4 py-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 transition hover:border-slate-300 dark:hover:border-slate-700 flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total de Registros</span>
            <div class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ number_format(method_exists($dados, 'total') ? $dados->total() : $dados->count()) }}
            </div>
        </div>
        <div class="text-slate-400 dark:text-slate-500 text-lg">
            <i class="ri-database-2-line"></i>
        </div>
    </div>

    <!-- Total de Colunas -->
    <div class="bg-white dark:bg-slate-900 px-4 py-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 transition hover:border-slate-300 dark:hover:border-slate-700 flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total de Colunas</span>
            <div class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ count($colunas ?? []) }}
            </div>
        </div>
        <div class="text-slate-400 dark:text-slate-500 text-lg">
            <i class="ri-layout-column-line"></i>
        </div>
    </div>

    <!-- Paginação Atual -->
    <div class="bg-white dark:bg-slate-900 px-4 py-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 transition hover:border-slate-300 dark:hover:border-slate-700 sm:col-span-2 lg:col-span-1 flex items-center justify-between">
        <div class="space-y-1">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Página Atual</span>
            <div class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white flex items-baseline gap-1.5">
                <span>{{ method_exists($dados, 'currentPage') ? $dados->currentPage() : 1 }}</span>
                <span class="text-xs font-normal text-slate-400">de {{ method_exists($dados, 'lastPage') ? $dados->lastPage() : 1 }}</span>
            </div>
        </div>
        <div class="text-slate-400 dark:text-slate-500 text-lg">
            <i class="ri-pages-line"></i>
        </div>
    </div>
</div>