<div x-show="modalHistoricoOpen" 
     class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
     x-cloak 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div @click.away="modalHistoricoOpen = false" 
         class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="p-4 px-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand-50 dark:bg-brand-950/50 flex items-center justify-center text-brand-600 dark:text-brand-400">
                    <i class="ri-time-line text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Linha do Tempo do Trabalhador</h3>
                        <span x-show="historicoTrabalhador.length > 0" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-brand-100 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 font-mono" x-text="historicoTrabalhador.length + ' evento(s)'"></span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Histórico de envios e processamentos no eSocial</p>
                </div>
            </div>
            <button @click="modalHistoricoOpen = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-4">
            <div x-show="carregandoHistorico" class="flex flex-col items-center justify-center py-16 space-y-4">
                <div class="w-10 h-10 border-4 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Buscando histórico de eventos no eSocial...</p>
            </div>

            <div x-show="!carregandoHistorico">
                <template x-if="historicoTrabalhador.length === 0">
                    <div class="text-center py-16 px-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                            <i class="ri-history-line"></i>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">Nenhum evento histórico encontrado</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">Não foram identificados registros de transmissão associados a este identificador.</p>
                    </div>
                </template>

                <template x-if="historicoTrabalhador.length > 0">
                    <div class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-4 space-y-6 pb-2">
                        <template x-for="(evento, index) in historicoTrabalhador" :key="evento.historico_id || index">
                            <div class="relative pl-7 group">
                                <div class="absolute -left-[17px] top-1.5 w-8 h-8 rounded-full border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold shadow-sm transition"
                                    :class="{
                                        'bg-emerald-500 text-white': evento.status_tipo === 'success',
                                        'bg-rose-500 text-white': evento.status_tipo === 'error',
                                        'bg-amber-500 text-white': evento.status_tipo === 'warning',
                                        'bg-gray-400 text-white': !evento.status_tipo || evento.status_tipo === 'default'
                                    }">
                                    <i :class="evento.status_icone || 'ri-circle-fill'"></i>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 rounded-xl p-4 shadow-sm hover:shadow transition">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="px-2 py-0.5 rounded text-xs font-bold font-mono uppercase"
                                                :class="{
                                                    'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300': evento.status_tipo === 'success',
                                                    'bg-rose-100 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300': evento.status_tipo === 'error',
                                                    'bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300': evento.status_tipo === 'warning',
                                                    'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200': !evento.status_tipo || evento.status_tipo === 'default'
                                                }" x-text="evento.evento"></span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="evento.titulo || ('Evento ' + evento.evento)"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs font-mono text-gray-500 dark:text-gray-400 shrink-0">
                                            <i class="ri-calendar-event-line"></i>
                                            <span x-text="evento.data"></span>
                                        </div>
                                    </div>

                                    <template x-if="evento.dados_evento">
                                        <button @click="registroParaEditar = JSON.parse(JSON.stringify(evento.dados_evento)); modalHistoricoOpen = false;"
                                            title="Editar Registro Original"
                                            class="px-2.5 py-1 text-xs font-semibold bg-amber-500/10 hover:bg-amber-500 text-amber-600 dark:text-amber-400 hover:text-white rounded-md border border-amber-500/30 transition flex items-center gap-1.5 ml-auto">
                                            <i class="ri-pencil-line"></i>
                                            <span>Editar Registro</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <div class="p-4 px-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
            <button @click="modalHistoricoOpen = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition">Fechar</button>
        </div>
    </div>
</div>