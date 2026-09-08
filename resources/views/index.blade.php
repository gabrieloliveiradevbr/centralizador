@extends('layout.app')

@section('content')
    @if ($dashboard->eventTitle)
        <div class="bg-brand-50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-800 p-4 rounded-xl flex items-start gap-4 shadow-sm">
            <div class="p-2 bg-brand-500 text-white rounded-lg shrink-0">
                <i class="ri-information-line text-xl"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-brand-900 dark:text-brand-200">{{ $dashboard->eventTitle }}</h4>
                <p class="text-xs text-brand-700 dark:text-brand-400 mt-1">{{ $dashboard->eventDescription }}</p>
            </div>
        </div>
    @endif

    <!-- Cards KPI -->
    @include('components.kpis')

    <!-- Tabela Principal com Busca e Filtros -->
    @include('components.tabela')
@endsection