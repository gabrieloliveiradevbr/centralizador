<div x-show="registroParaEditar !== null"
     class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm flex items-center justify-center p-4" 
     x-cloak 
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="registroParaEditar = null"
         class="bg-white dark:bg-slate-900 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <form action="{{ route('visualizar.update', ['banco' => $banco, 'schema' => $schema ?? 'esocial', 'tabela' => $tabela ?? '']) }}"
            method="POST" class="flex flex-col h-full">
            @csrf
            @method('PUT')

            <input type="hidden" name="_primary_key_name" value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
            <input type="hidden" name="_primary_key_value" :value="registroParaEditar ? registroParaEditar['{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] : ''">

            <div class="h-14 px-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-900 dark:text-white">Editar Registro</span>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $tabela }}</span>
                </div>
                <button type="button" @click="registroParaEditar = null" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                <template x-if="registroParaEditar">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="([coluna, valor]) in Object.entries(registroParaEditar || {})" :key="coluna">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-mono uppercase text-slate-500 dark:text-slate-400 truncate" x-text="coluna"></label>
                                    <span x-show="coluna === '{{ $dashboard->primaryKey }}'" class="text-[9px] px-1 rounded font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">PK</span>
                                </div>

                                <div x-show="coluna === '{{ $dashboard->primaryKey }}'">
                                    <input type="text" :value="valor" disabled
                                        class="w-full p-2 text-xs bg-slate-50 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg font-mono cursor-not-allowed">
                                </div>

                                <div x-show="coluna !== '{{ $dashboard->primaryKey }}'">
                                    <textarea x-show="coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao')"
                                        x-model="registroParaEditar[coluna]" rows="3"
                                        class="w-full p-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:border-slate-400 dark:focus:border-slate-600 font-mono outline-none transition"></textarea>

                                    <input x-show="!(coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao'))"
                                        type="text" x-model="registroParaEditar[coluna]"
                                        class="w-full p-2 text-xs bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:border-slate-400 dark:focus:border-slate-600 font-mono outline-none transition">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="p-4 px-6 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                <button type="button" @click="registroParaEditar = null" class="px-3.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition">
                    Cancelar
                </button>
                <button type="submit" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 rounded-lg text-xs font-medium transition shadow-sm">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>