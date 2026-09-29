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
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Fira Code', 'Menlo', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased font-sans min-h-screen flex flex-col selection:bg-brand-500 selection:text-white">

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-4 right-4 z-50 bg-slate-900/90 dark:bg-slate-100/95 backdrop-blur text-white dark:text-slate-900 px-4 py-2.5 rounded-lg shadow-lg border border-slate-700/30 dark:border-slate-300 flex items-center gap-2.5 transition text-xs font-medium">
            <i class="ri-checkbox-circle-fill text-emerald-400 dark:text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="fixed top-4 right-4 z-50 bg-rose-600/90 text-white backdrop-blur px-4 py-2.5 rounded-lg shadow-lg border border-rose-500/50 flex items-center gap-2.5 transition text-xs font-medium">
            <i class="ri-error-warning-fill text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex h-screen overflow-hidden">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @include('components.header')

            <main class="flex-1 overflow-y-auto p-5 lg:p-6 space-y-5" :class="{ 'p-3 space-y-3': telaCheia }">
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
