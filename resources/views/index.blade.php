@extends('layout.app')

@section('content')
    @if ($dashboard->eventTitle)
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-xl px-5 py-4 flex items-start justify-between gap-4 transition">
            <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        {{ strtoupper($tabela) }}
                    </span>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                        {{ $dashboard->eventTitle }}
                    </h3>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-4xl">
                    {{ $dashboard->eventDescription }}
                </p>
            </div>
            <div class="hidden sm:flex items-center text-slate-400 dark:text-slate-500 shrink-0">
                <i class="ri-information-line text-lg"></i>
            </div>
        </div>
    @endif

    <!-- Cards KPI Minimalistas -->
    @include('components.kpis')

    <!-- Tabela Principal com Busca e Filtros -->
    @include('components.tabela')
@endsection
