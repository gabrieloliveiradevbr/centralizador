<div x-show="registroSelecionado !== null" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak x-transition>
    <div @click.away="registroSelecionado = null" class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center gap-2">
                <i class="ri-file-text-line text-brand-500 text-xl"></i>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Detalhes do Registro - <span class="text-brand-500">{{ $tabela }}</span></h3>
            </div>
            <button @click="registroSelecionado = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <i class="ri-close-line text-2xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto space-y-3 flex-1">
            <template x-for="(valor, chave) in registroSelecionado" :key="chave">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/50 items-start">
                    <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 break-words" x-text="chave"></span>
                    <div class="md:col-span-2 text-sm text-gray-800 dark:text-gray-200 font-mono break-all">
                        <template x-if="valor === null"><span class="text-xs text-gray-400 italic">null</span></template>
                        <template x-if="typeof valor === 'boolean'">
                            <span :class="valor ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" x-text="valor ? 'Verdadeiro' : 'Falso'"></span>
                        </template>
                        <template x-if="typeof valor === 'object' && valor !== null">
                            <pre class="text-xs p-2 bg-gray-200 dark:bg-gray-950 rounded text-green-600 dark:text-green-400 overflow-x-auto" x-text="JSON.stringify(valor, null, 2)"></pre>
                        </template>
                        <template x-if="valor !== null && typeof valor !== 'boolean' && typeof valor !== 'object'">
                            <span x-text="valor"></span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        <div class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
            <div>
                <template x-if="registroSelecionado && (registroSelecionado.idevento || registroSelecionado.cpftrab || registroSelecionado.cpfbenef || registroSelecionado.cpf || registroSelecionado.matricula || registroSelecionado.num_cpf_trab || registroSelecionado.evento_id || registroSelecionado.id)">
                    <button @click="carregarHistoricoTrabalhador(registroSelecionado.idevento || registroSelecionado.cpftrab || registroSelecionado.cpfbenef || registroSelecionado.cpf || registroSelecionado.matricula || registroSelecionado.num_cpf_trab || registroSelecionado.evento_id || registroSelecionado.id)" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-medium transition flex items-center gap-2 shadow-sm">
                        <i class="ri-time-line"></i> Ver Linha do Tempo
                    </button>
                </template>
            </div>
            <button @click="registroSelecionado = null" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">Fechar</button>
        </div>
    </div>
</div>