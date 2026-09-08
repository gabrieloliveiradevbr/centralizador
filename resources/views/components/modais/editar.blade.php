<div x-show="registroParaEditar !== null"
    class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak x-transition>
    <div @click.away="registroParaEditar = null"
        class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form action="{{ route('visualizar.update', ['banco' => $banco, 'schema' => $schema, 'tabela' => $tabela]) }}"
            method="POST" class="flex flex-col h-full">
            @csrf
            @method('PUT')

            <input type="hidden" name="_primary_key_name" value="{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}">
            <input type="hidden" name="_primary_key_value" :value="registroParaEditar ? registroParaEditar['{{ $dashboard->primaryKey ?? ($colunas[0] ?? 'id') }}'] : ''">

            <div class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2">
                    <i class="ri-pencil-line text-amber-500 text-xl"></i>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Editar Registro - <span class="text-amber-500">{{ $tabela }}</span>
                    </h3>
                </div>
                <button type="button" @click="registroParaEditar = null" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                    <template x-for="([coluna, valor]) in Object.entries(registroParaEditar)" :key="coluna">
                        <div class="space-y-1" :class="coluna === '{{ $dashboard->primaryKey }}' ? 'bg-amber-50 dark:bg-amber-900/20 p-2 rounded-lg border border-amber-100 dark:border-amber-800/50' : ''">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400" x-text="coluna"></label>

                            <div x-show="coluna === '{{ $dashboard->primaryKey }}'">
                                <input type="text" :value="valor" disabled
                                    class="w-full p-2 text-sm bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 rounded-lg font-mono cursor-not-allowed">
                            </div>

                            <div x-show="coluna !== '{{ $dashboard->primaryKey }}'">
                                <textarea x-show="coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao')"
                                    x-model="registroParaEditar[coluna]" rows="3"
                                    class="w-full p-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono outline-none transition"></textarea>

                                <input x-show="!(coluna.toLowerCase().includes('message') || coluna.toLowerCase().includes('obs') || coluna.toLowerCase().includes('descricao'))"
                                    type="text" x-model="registroParaEditar[coluna]"
                                    class="w-full p-2 text-sm bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-amber-500 font-mono outline-none transition">
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-3">
                <button type="button" @click="registroParaEditar = null" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <i class="ri-save-line"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>