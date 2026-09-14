@extends('layout.app_certificado')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ uploading: false }">

    @if ($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 p-4 rounded-xl text-xs space-y-1 shadow-sm">
            <div class="font-bold flex items-center gap-1.5 text-sm">
                <i class="ri-error-warning-fill text-base text-rose-500"></i> Erro na Importação:
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

    @if (session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 p-4 rounded-xl text-xs flex items-center gap-2 shadow-sm">
            <i class="ri-checkbox-circle-fill text-base text-emerald-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Cabeçalho & Seletor de Banco -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('visualizar', ['banco' => $banco]) }}"
               class="p-2 text-gray-500 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition"
               title="Voltar para o Visualizador">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="ri-upload-cloud-2-line text-brand-500"></i> Importação de XML eSocial
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Selecione o evento correspondente e faça o upload do arquivo XML.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <i class="ri-server-line text-gray-400"></i>
            <select onchange="location = this.value;"
                class="bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-800 dark:text-gray-200 text-sm rounded-lg p-2 focus:ring-brand-500 outline-none">
                @foreach ($bancosDisponiveis as $key => $nome)
                    <option value="{{ route('xml.import.view', ['banco' => $key]) }}"
                        {{ $banco === $key ? 'selected' : '' }}>
                        {{ $nome }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Card de Importação -->
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm max-w-2xl mx-auto">
        <form action="{{ route('xml.import', ['banco' => $banco]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Seleção do Evento -->
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-2">
                    Evento do XML (Tabela de Destino)
                </label>
                <select name="tabela" required
                    class="w-full p-2.5 text-sm bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="" disabled selected>Selecione o evento correspondente...</option>
                    @foreach ($eventos as $evento)
                        <option value="{{ $evento['valor'] }}">{{ $evento['label'] }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1.5 italic">
                    Escolha a tabela onde os dados do XML devem ser inseridos.
                </p>
            </div>

            <!-- Upload do Arquivo -->
            <div>
                <label class="block text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-2">
                    Arquivo XML
                </label>
                <div class="relative group">
                    <input type="file" name="xml_file" accept=".xml" required
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/40 dark:file:text-brand-300 cursor-pointer">
                </div>
                <p class="text-[11px] text-gray-400 mt-1.5 italic">
                    Apenas arquivos com extensão .xml são aceitos. Tamanho máximo: 5MB.
                </p>
            </div>

            <!-- Botão de Ação -->
            <div class="pt-4">
                <button type="submit"
                    :disabled="uploading"
                    @click="uploading = true"
                    class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <i :class="uploading ? 'ri-loader-4-line animate-spin' : 'ri-upload-cloud-2-line'" class="text-lg"></i>
                    <span x-text="uploading ? 'Importando...' : 'Importar XML para o Banco'"></span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
