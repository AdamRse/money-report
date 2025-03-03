@extends('layouts.app')

@section('title', 'Récapitulatif des revenus')

@section('content')
<div class="report-container">
    <header class="report-header">
        <h1>Récapitulatif des revenus</h1>

        @if(isset($periodMessage) && !empty($periodMessage))
            <div class="period-message">{{ $periodMessage }}</div>
        @endif
    </header>

    <!-- Filtres -->
    <section class="filters-card">
        <h2 class="filters-title">Filtrer les revenus</h2>
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div class="alert-message">{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('incomes.report') }}" method="GET" class="filters-form" id="filterForm">
            <div class="filters-type">
                <div class="form-group">
                    <label for="filter_type" class="form-label">Type de filtre</label>
                    <select name="filter_type" id="filter_type" class="form-select">
                        <option value="">Tous les revenus</option>
                        <option value="period" {{ request('filter_type') === 'period' ? 'selected' : '' }}>De date à date</option>
                        <option value="month" {{ request('filter_type') === 'month' ? 'selected' : '' }}>Sélectionner un mois</option>
                    </select>
                </div>

                <!-- Filtre période -->
                <div id="periodFilter" style="display: {{ request('filter_type') === 'period' ? 'block' : 'none' }}">
                    <div class="period-inputs">
                        <div class="form-group">
                            <label for="start_date" class="form-label">De</label>
                            <input type="date"
                                   id="start_date"
                                   name="start_date"
                                   value="{{ request('start_date') }}"
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="form-label">À</label>
                            <input type="date"
                                   id="end_date"
                                   name="end_date"
                                   value="{{ request('end_date') }}"
                                   class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Filtre mois unique -->
                <div id="monthFilter" style="display: {{ request('filter_type') === 'month' ? 'block' : 'none' }}">
                    <div class="month-selects">
                        <div class="form-group">
                            <label for="month_select" class="form-label">Mois</label>
                            <select id="month_select" name="month_number" class="form-select">
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}"
                                            {{ request('month_number') == str_pad($month, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($month)->locale('fr')->monthName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="year_select" class="form-label">Année</label>
                            <select id="year_select" name="year_number" class="form-select">
                                @foreach(range(date('Y'), date('Y')-5) as $year)
                                    <option value="{{ $year }}"
                                            {{ request('year_number') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filters-actions">
                <button type="submit" class="btn btn-primary">Appliquer</button>
                <a href="{{ route('incomes.report') }}" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </form>
    </section>

    <!-- Tableau de bord statistique -->
    <section class="dashboard-grid">
        <!-- Tuiles statistiques principales -->
        <div class="stats-grid">
            <div class="stats-card">
                <span class="stats-label">Total des revenus</span>
                <span class="stats-value">{{ number_format($statistics['total'], 2, ',', ' ') }} €</span>
            </div>
            <div class="stats-card highlight">
                <span class="stats-label">Total taxable</span>
                <span class="stats-value">{{ number_format($statistics['total_taxable'], 2, ',', ' ') }} €</span>
                <span class="stats-percentage">
                    @if($statistics['total'] > 0)
                        ({{ number_format(($statistics['total_taxable'] / $statistics['total']) * 100, 1) }}%)
                    @endif
                </span>
            </div>
            <div class="stats-card highlight">
                <span class="stats-label">Total à déclarer</span>
                <span class="stats-value">{{ number_format($statistics['total_must_declare'], 2, ',', ' ') }} €</span>
                <span class="stats-percentage">
                    @if($statistics['total'] > 0)
                        ({{ number_format(($statistics['total_must_declare'] / $statistics['total']) * 100, 1) }}%)
                    @endif
                </span>
            </div>
            <div class="stats-card">
                <span class="stats-label">Nombre de revenus</span>
                <span class="stats-value">{{ $statistics['count'] }}</span>
            </div>
            <div class="stats-card">
                <span class="stats-label">Moyenne par revenu</span>
                <span class="stats-value">{{ number_format($statistics['average'], 2, ',', ' ') }} €</span>
            </div>
        </div>

        <!-- Visualisation des répartitions par type -->
        @if($statistics['by_type']->isNotEmpty())
        <section class="types-breakdown">
            <h2>Répartition par type de revenu</h2>

            <div class="types-grid">
                @foreach($statistics['by_type'] as $type => $typeStats)
                    <div class="type-stat">
                        <div class="type-header">
                            <span class="type-name">{{ $type }}</span>
                            <span class="type-percentage">
                                @if($statistics['total'] > 0)
                                    {{ number_format(($typeStats['total'] / $statistics['total']) * 100, 1) }}%
                                @endif
                            </span>
                        </div>
                        <div class="type-bar">
                            <div class="type-bar-fill" style="width: {{ $statistics['total'] > 0 ? ($typeStats['total'] / $statistics['total']) * 100 : 0 }}%;"></div>
                        </div>
                        <div class="type-info">
                            <span class="type-total">{{ number_format($typeStats['total'], 2, ',', ' ') }} €</span>
                            <span class="type-count">{{ $typeStats['count'] }} revenus</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif
    </section>

    <!-- Tableau détaillé des revenus -->
    @if($incomes->isNotEmpty())
    <section class="revenus-card">
        <h2>Détail des revenus</h2>
        @include('components.tables.incomes', ['incomes' => $incomes, 'showActions' => false, 'showTaxInfo' => true])
    </section>
    @else
    <div class="empty-state">
        @if(isset($periodMessage) && !empty($periodMessage))
            <p>{{ $periodMessage }}</p>
        @else
            <p>Aucun revenu enregistré</p>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filter_type');
    const periodFilter = document.getElementById('periodFilter');
    const monthFilter = document.getElementById('monthFilter');

    function updateFilters() {
        const selectedValue = filterType.value;

        periodFilter.style.display = 'none';
        monthFilter.style.display = 'none';

        if (selectedValue === 'period') {
            periodFilter.style.display = 'block';
        } else if (selectedValue === 'month') {
            monthFilter.style.display = 'block';
        }
    }

    filterType.addEventListener('change', updateFilters);
    updateFilters();
});
</script>
@endpush

<style>
.report-container {
    max-width: 1200px;
    margin: 0 auto;
}

.report-header {
    margin-bottom: 2rem;
}

.period-message {
    color: var(--gray-dark);
    font-style: italic;
    margin-top: 0.5rem;
}

.dashboard-grid {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stats-card.highlight {
    background-color: #e3f2fd;
    border-left: 4px solid var(--primary-color);
}

.stats-percentage {
    font-size: 0.9rem;
    color: var(--gray-dark);
    display: block;
    margin-top: 0.25rem;
}

.types-breakdown {
    background: white;
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.type-stat {
    background: var(--gray-light);
    border-radius: var(--radius);
    padding: 1.5rem;
}

.type-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.type-name {
    font-weight: 500;
    font-size: 1.1rem;
}

.type-percentage {
    font-weight: 600;
    color: var(--primary-color);
}

.type-bar {
    height: 8px;
    background: #ddd;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.type-bar-fill {
    height: 100%;
    background: var(--primary-color);
    border-radius: 4px;
}

.type-info {
    display: flex;
    justify-content: space-between;
}

.type-total {
    font-weight: 600;
    color: var(--primary-color);
}

.type-count {
    color: var(--gray-dark);
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .types-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
