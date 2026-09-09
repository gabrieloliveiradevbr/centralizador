<div x-show="registroParaDeletar !== null" 
     class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
     x-cloak 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="registroParaDeletar = null" 
         class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4 text-center"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl flex items-center justify-center mx-auto text-2xl shadow-sm">
            <i class="ri-delete-bin-line"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Confirmar Exclusão</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Esta ação removerá permanentemente o registro da base de dados PostgreSQL. Esta ação não pode ser desfeita.
            </p>
        </div>
        <form action="{{ route('visualizar.destroy', ['banco' => $banco, 'schema' => $schema ?? 'esocial', 'tabela' => $tabela ?? '']) }}" method="POST" class="flex justify-center gap-2.5 pt-2">
            @csrf
            @method('DELETE')
            <input type="hidden" name="_primary_key_name" value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
            <input type="hidden" name="_primary_key_value" :value="registroParaDeletar ? registroParaDeletar['{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] : ''">
            <button type="button" @click="registroParaDeletar = null" class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Cancelar
            </button>
            <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <i class="ri-delete-bin-line"></i> Excluir Registro
            </button>
        </form>
    </div>
</div>