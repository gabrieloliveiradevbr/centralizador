@extends('layout.app')

@section('content')
    @if ($dashboard->eventTitle)
        <div class="bg-gradient-to-r from-brand-100/60 via-brand-50/20 to-white dark:from-brand-900/30 dark:via-brand-950/20 dark:to-gray-900/80 border border-brand-300/60 dark:border-brand-700/40 p-4 rounded-xl flex items-start gap-3.5 shadow-sm dark:shadow-brand-950/20">
            <div class="w-9 h-9 bg-brand-500 text-white rounded-lg flex items-center justify-center shrink-0 shadow-sm shadow-brand-500/20">
                <i class="ri-information-line text-lg"></i>
            </div>
            <div class="space-y-0.5">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white tracking-tight">{{ $dashboard->eventTitle }}</h4>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $dashboard->eventDescription }}</p>
            </div>
        </div>
    @endif

    <!-- Cards KPI -->
    @include('components.kpis')

    <!-- Tabela Principal com Busca e Filtros -->
    @include('components.tabela')
@endsection
