<div x-show="registroSelecionado !== null" 
     class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
     x-cloak 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="registroSelecionado = null" 
         class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                    <i class="ri-file-text-line text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Detalhes do Registro</h3>
                    <p class="text-xs text-brand-600 dark:text-brand-400 font-mono">{{ $tabela }}</p>
                </div>
            </div>
            <button @click="registroSelecionado = null" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        <div class="p-5 overflow-y-auto space-y-2 flex-1 divide-y divide-gray-100 dark:divide-gray-700/60">
            <template x-for="(valor, chave) in registroSelecionado" :key="chave">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 pt-2.5 first:pt-0 items-start text-xs">
                    <span class="font-semibold text-gray-500 dark:text-gray-400 font-mono break-words" x-text="chave"></span>
                    <div class="md:col-span-2 text-gray-800 dark:text-gray-200 font-mono break-all flex items-start justify-between gap-2">
                        <div>
                            <template x-if="valor === null">
                                <span class="px-1.5 py-0.2 rounded text-[10px] text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 font-sans">null</span>
                            </template>
                            <template x-if="typeof valor === 'boolean'">
                                <span :class="valor ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300'" class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-sans font-semibold border" x-text="valor ? 'TRUE' : 'FALSE'"></span>
                            </template>
                            <template x-if="typeof valor === 'object' && valor !== null">
                                <pre class="text-[11px] p-2 bg-gray-100 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 text-emerald-600 dark:text-emerald-400 overflow-x-auto" x-text="JSON.stringify(valor, null, 2)"></pre>
                            </template>
                            <template x-if="valor !== null && typeof valor !== 'boolean' && typeof valor !== 'object'">
                                <span x-text="valor"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
            <div>
                <template x-if="registroSelecionado && (registroSelecionado.idevento || registroSelecionado.cpftrab || registroSelecionado.cpfbenef || registroSelecionado.cpf || registroSelecionado.matricula || registroSelecionado.num_cpf_trab || registroSelecionado.evento_id || registroSelecionado.id)">
                    <button @click="carregarHistoricoTrabalhador(registroSelecionado.idevento || registroSelecionado.cpftrab || registroSelecionado.cpfbenef || registroSelecionado.cpf || registroSelecionado.matricula || registroSelecionado.num_cpf_trab || registroSelecionado.evento_id || registroSelecionado.id)" 
                            class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                        <i class="ri-time-line"></i> Ver Linha do Tempo
                    </button>
                </template>
            </div>
            <button @click="registroSelecionado = null" 
                    class="px-4 py-1.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Fechar
            </button>
        </div>
    </div>
</div>