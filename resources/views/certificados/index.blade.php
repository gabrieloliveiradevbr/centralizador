@extends('layout.app_certificado')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ tipoTitular: 'entidade', modalDeletarDoc: null }">

    @if ($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 p-4 rounded-xl text-xs space-y-1 shadow-sm">
            <div class="font-bold flex items-center gap-1.5 text-sm">
                <i class="ri-error-warning-fill text-base text-rose-500"></i> Atenção aos dados informados:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 p-4 rounded-xl text-xs flex items-center gap-2 shadow-sm">
            <i class="ri-error-warning-fill text-base text-rose-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Cabeçalho & Seletor de Banco -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('visualizar', ['banco' => $banco]) }}"
               class="p-2 text-gray-500 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition"
               title="Voltar para as tabelas">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-shield-keyhole-line text-brand-500"></i> Gestão de Certificado Digital
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Certificados A1 para assinatura do eSocial (Entidade Própria ou Procuração Eletrônica).
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <i class="ri-server-line text-gray-400"></i>
            <select onchange="location = this.value;"
                class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 text-sm rounded-lg p-2 focus:ring-brand-500">
                @foreach ($bancosDisponiveis as $key => $nome)
                    <option value="{{ route('certificados.index', ['banco' => $key]) }}"
                        {{ $banco === $key ? 'selected' : '' }}>
                        {{ $nome }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Lista / Status dos Certificados Cadastrados -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="ri-information-line text-brand-500"></i> Certificados Cadastrados
            </h3>

            <div class="space-y-3 font-mono text-xs">
                @forelse ($certificados as $cert)
                    @php
                        $docSanitizado = preg_replace('/[^0-9]/', '', $cert->cnpj);
                        $arquivoExiste = collect($arquivosStorage)->contains(fn($p) => str_contains($p, $docSanitizado));
                        $isEntidade = $docSanitizado === preg_replace('/[^0-9]/', '', $cnpjEntidade);
                    @endphp

                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-sans {{ $isEntidade ? 'bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' }}">
                                {{ $isEntidade ? 'Titular Entidade' : 'Procurador' }}
                            </span>
                            <span class="text-gray-400 font-normal">ID #{{ $cert->id }}</span>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Documento (CPF/CNPJ):</span>
                            <span class="text-gray-900 dark:text-white font-bold text-sm">{{ $cert->cnpj }}</span>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-[11px]">
                            <span class="flex items-center gap-1.5 font-sans font-medium {{ $arquivoExiste ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                <i class="{{ $arquivoExiste ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill' }}"></i>
                                {{ $arquivoExiste ? 'Arquivo físico no servidor' : 'Arquivo físico ausente' }}
                            </span>

                            <div class="flex items-center gap-2">
                                <!-- Download -->
                                @if ($arquivoExiste)
                                    <a href="{{ route('certificados.download', ['banco' => $banco, 'doc' => $cert->cnpj]) }}"
                                        class="p-1.5 bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg hover:bg-emerald-100 transition"
                                        title="Baixar Certificado">
                                        <i class="ri-download-cloud-2-line text-base"></i>
                                    </a>
                                @endif

                                <!-- Excluir -->
                                <button type="button" @click="modalDeletarDoc = '{{ $cert->cnpj }}'"
                                    class="p-1.5 bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 rounded-lg hover:bg-red-100 transition"
                                    title="Excluir Certificado">
                                    <i class="ri-delete-bin-line text-base"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 font-sans">
                        <i class="ri-shield-cross-line text-3xl block mb-2"></i>
                        Nenhum certificado digital cadastrado nesta base.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Formulário de Envio (Entidade / Procurador) -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <i class="ri-upload-cloud-line text-brand-500"></i> Enviar Novo Certificado (.pfx / .p12)
            </h3>

            <form action="{{ route('certificados.upload', ['banco' => $banco]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Opção de Tipo de Titular -->
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Tipo de Assinatura
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3 border rounded-lg cursor-pointer flex items-center gap-2 text-xs font-medium transition"
                            :class="tipoTitular === 'entidade' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300 font-bold' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                            <input type="radio" name="tipo_titular" value="entidade" x-model="tipoTitular" class="text-brand-500">
                            <span>Titular da Entidade</span>
                        </label>

                        <label class="p-3 border rounded-lg cursor-pointer flex items-center gap-2 text-xs font-medium transition"
                            :class="tipoTitular === 'procuracao' ? 'border-purple-500 bg-purple-50/50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 font-bold' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400'">
                            <input type="radio" name="tipo_titular" value="procuracao" x-model="tipoTitular" class="text-purple-500">
                            <span>Procurador Eletrônico</span>
                        </label>
                    </div>
                </div>

                <!-- Campo de Documento (Exibido apenas na opção Procurador) -->
                <div x-show="tipoTitular === 'procuracao'" x-collapse>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        CPF ou CNPJ do Procurador
                    </label>
                    <input type="text" name="documento" placeholder="Digite o CPF ou CNPJ..."
                        class="w-full p-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-purple-500 outline-none font-mono">
                    <p class="text-[11px] text-gray-400 mt-1">Informe o CPF do contador/responsável ou CNPJ da assessoria que possui a procuração no eSocial.</p>
                </div>

                <div x-show="tipoTitular === 'entidade'" x-collapse>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        CNPJ da Entidade (Automático)
                    </label>
                    <input type="text" value="{{ $cnpjEntidade }}" disabled
                        class="w-full p-2 text-sm bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-500 font-mono cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Arquivo do Certificado (.pfx / .p12)
                    </label>
                    <input type="file" name="certificado" accept=".pfx,.p12" required
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/40 dark:file:text-brand-300">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-1">
                        Senha
                    </label>
                    <input type="password" name="senha" placeholder="••••••••"
                        class="w-full p-2 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-medium text-sm rounded-lg transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="ri-save-line text-lg"></i> Salvar e Associar
                </button>
            </form>
        </div>

    </div>

    <!-- Modal Confirmar Exclusão -->
    <div x-show="modalDeletarDoc !== null" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak x-transition>
        <div @click.away="modalDeletarDoc = null" class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4 text-center">
            <div class="p-3 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full w-12 h-12 flex items-center justify-center mx-auto text-2xl">
                <i class="ri-error-warning-line"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Excluir Certificado</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Tem certeza que deseja apagar o certificado vinculado ao documento <strong class="font-mono text-red-500" x-text="modalDeletarDoc"></strong>? Esta ação removerá o registro do banco e o arquivo físico.
                </p>
            </div>
            <form action="{{ route('certificados.destroy', ['banco' => $banco]) }}" method="POST" class="flex justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <input type="hidden" name="documento" :value="modalDeletarDoc">

                <button type="button" @click="modalDeletarDoc = null" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-medium hover:bg-gray-300 transition">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <i class="ri-delete-bin-line"></i> Confirmar Exclusão
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
