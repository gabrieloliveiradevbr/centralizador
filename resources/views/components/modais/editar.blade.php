<div x-show="registroParaEditar !== null"
     class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
     x-cloak 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="registroParaEditar = null"
         class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <form action="{{ route('visualizar.update', ['banco' => $banco, 'schema' => $schema ?? 'esocial', 'tabela' => $tabela ?? '']) }}"
            method="POST" class="flex flex-col h-full">
            @csrf
            @method('PUT')

            <input type="hidden" name="_primary_key_name" value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
            <input type="hidden" name="_primary_key_value" :value="registroParaEditar ? registroParaEditar['{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] : ''">

            <div class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="ri-pencil-line text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Editar Registro</h3>
                        <p class="text-xs text-amber-600 dark:text-amber-400 font-mono">{{ $tabela }}</p>
                    </div>
                </div>
                <button type="button" @click="registroParaEditar = null" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="([coluna, valor]) in Object.entries(registroParaEditar)" :key="coluna">
                        <div class="space-y-1.5" :class="coluna === '{{ $dashboard->primaryKey }}' ? 'bg-amber-50/60 dark:bg-amber-950/20 p-2.5 rounded-lg border border-amber-200/60 dark:border-amber-800/40' : ''">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 truncate" x-text="coluna"></label>
                                <span x-show="coluna === '{{ $dashboard->primaryKey }}'" class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase bg-amber-200 dark:bg-amber-900 text-amber-800 dark:text-amber-200">PK</span>
                            </div>

                            <div x-show="coluna === '{{ $dashboard->primaryKey }}'">
                                <input type="text" :value="valor" disabled
                                    class="w-full p-2 text-xs bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg font-mono cursor-not-allowed">
                            </div>

                            <div x-show="coluna !== '{{ $dashboard->primaryKey }}'">
                                <textarea x-show="coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao')"
                                    x-model="registroParaEditar[coluna]" rows="3"
                                    class="w-full p-2 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono outline-none transition"></textarea>

                                <input x-show="!(coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao'))"
                                    type="text" x-model="registroParaEditar[coluna]"
                                    class="w-full p-2 text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono outline-none transition">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-2.5">
                <button type="button" @click="registroParaEditar = null" class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                    <i class="ri-save-line"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>