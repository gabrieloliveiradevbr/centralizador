<!DOCTYPE html>
<html lang="pt-BR" x-data="{
    darkMode: {{ ($darkMode ?? request()->cookie('dark_mode') === '1') ? 'true' : 'false' }},
    sidebarOpen: true,
    registroSelecionado: null,
    registroParaEditar: null,
    registroParaDeletar: null,
    painelFiltrosOpen: {{ !empty($filtrosColuna) ? 'true' : 'false' }},
    painelDuplicadosOpen: {{ !empty($colunaDuplicada) ? 'true' : 'false' }},
    telaCheia: false,
    historicoTrabalhador: [],
    carregandoHistorico: false,
    modalHistoricoOpen: false,
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        document.cookie = 'dark_mode=' + (this.darkMode ? '1' : '0') + '; path=/; max-age=' + (60 * 60 * 24 * 365);
    },
    async carregarHistoricoTrabalhador(id) {
        if (!id) return;
        this.carregandoHistorico = true;
        this.modalHistoricoOpen = true;
        this.historicoTrabalhador = [];

        try {
            const encodedId = encodeURIComponent(String(id).trim());
            const response = await fetch(`{{ url('/') }}/{{ $banco }}/trabalhador/${encodedId}/historico`);
            const result = await response.json();
            if (result.success) {
                this.historicoTrabalhador = result.data;
            } else {
                alert('Erro ao carregar histórico: ' + (result.error || 'Erro desconhecido'));
                this.modalHistoricoOpen = false;
            }
        } catch (error) {
            alert('Erro na requisição: ' + error.message);
            this.modalHistoricoOpen = false;
        } finally {
            this.carregandoHistorico = false;
        }
    }
}" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralizador - Visualizador de Dados</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon_io/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#034d75',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased font-sans min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-4 right-4 z-50 bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transition">
            <i class="ri-checkbox-circle-fill text-xl"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex h-screen overflow-hidden">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <main class="flex-1 overflow-y-auto p-6 space-y-6" :class="{ 'p-3 space-y-3': telaCheia }">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- INCLUSÃO DAS MODAIS SEPARADAS -->
    @if (!empty($tabela))
        @include('components.modais.editar')
        @include('components.modais.deletar')
        @include('components.modais.detalhes')
        @include('components.modais.timeline')
    @endif

</body>
</html>
