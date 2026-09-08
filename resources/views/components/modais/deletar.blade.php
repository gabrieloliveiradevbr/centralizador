<div x-show="registroParaDeletar !== null" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak x-transition>
    <div @click.away="registroParaDeletar = null" class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4 text-center">
        <div class="p-3 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full w-12 h-12 flex items-center justify-center mx-auto text-2xl">
            <i class="ri-error-warning-line"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirmar Exclusão</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Esta ação removerá permanentemente o registro da base de dados PostgreSQL. Deseja continuar?
            </p>
        </div>
        <form action="{{ route('visualizar.destroy', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}" method="POST" class="flex justify-center gap-3 pt-2">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_primary_key_name" value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
            <input type="hidden" name="_primary_key_value" :value="registroParaDeletar ? registroParaDeletar['{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] : ''">
            <button type="button" @click="registroParaDeletar = null" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">Cancelar</button>
            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                <i class="ri-delete-bin-line"></i> Excluir Registro
            </button>
        </form>
    </div>
</div>