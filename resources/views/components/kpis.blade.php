<div x-show="!telaCheia" class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
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

    <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de Colunas</p>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ count($colunas ?? []) }}
            </h3>
        </div>
        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl">
            <i class="ri-layout-column-line text-2xl"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Página Atual</p>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ method_exists($dados, 'currentPage') ? $dados->currentPage() : 1 }} /
                {{ method_exists($dados, 'lastPage') ? $dados->lastPage() : 1 }}
            </h3>
        </div>
        <div class="p-3 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-xl">
            <i class="ri-file-copy-2-line text-2xl"></i>
        </div>
    </div>
</div>