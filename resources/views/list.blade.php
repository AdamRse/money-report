@extends('layouts.app')

@section('title', 'Liste des revenus')

@section('content')
    <div class="list-container">
        <h1>Liste des revenus</h1>

        <!-- Liste des revenus -->
        <div class="revenus-card">
            <h2>Détail des revenus</h2>
            @if($revenus->isEmpty())
                <div class="empty-state">
                    <p>Aucun revenu trouvé pour cette période</p>
                </div>
            @else
                <div class="table-container">
                    <table class="revenus-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($revenus as $revenu)
                                <tr>
                                    <td class="date-cell">
                                        {{ Carbon\Carbon::parse($revenu->date_revenu)->format('d/m/Y') }}
                                    </td>
                                    <td class="type-cell">{{ $revenu->typeRevenu->nom }}</td>
                                    <td class="amount-cell">
                                        {{ number_format($revenu->montant, 2, ',', ' ') }} €
                                    </td>
                                    <td class="notes-cell">{{ $revenu->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Filtres -->
        <div class="filters-card">
            <h2 class="filters-title">Filtrer les revenus</h2>
            <form action="{{ route('revenus.list') }}" method="GET" class="filters-form" id="filterForm">
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
                    <a href="{{ route('revenus.list') }}" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stats-card">
                <span class="stats-label">Total</span>
                <span class="stats-value">{{ number_format($stats['total'], 2, ',', ' ') }} €</span>
            </div>
            <div class="stats-card">
                <span class="stats-label">Nombre de revenus</span>
                <span class="stats-value">{{ $stats['count'] }}</span>
            </div>
            <div class="stats-card">
                <span class="stats-label">Moyenne par revenu</span>
                <span class="stats-value">{{ number_format($stats['average'], 2, ',', ' ') }} €</span>
            </div>
        </div>

        <!-- Répartition par type -->
        <div class="types-card">
            <h2>Répartition par type</h2>
            <div class="types-grid">
                @foreach($stats['by_type'] as $type => $typeStats)
                    <div class="type-stat">
                        <span class="type-name">{{ $type }}</span>
                        <span class="type-total">{{ number_format($typeStats['total'], 2, ',', ' ') }} €</span>
                        <span class="type-count">({{ $typeStats['count'] }} revenus)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Script chargé'); // Debug

        // Récupération des éléments
        const filterType = document.getElementById('filter_type');
        const periodFilter = document.getElementById('periodFilter');
        const monthFilter = document.getElementById('monthFilter');

        console.log('Elements :', {
            filterType: filterType,
            periodFilter: periodFilter,
            monthFilter: monthFilter
        }); // Debug

        // Fonction pour afficher/masquer les filtres
        function updateFilters() {
            const selectedValue = filterType.value;
            console.log('Filtre sélectionné :', selectedValue);

            // Cache tous les filtres
            periodFilter.style.display = 'none';
            monthFilter.style.display = 'none';

            // Affiche le filtre sélectionné
            if (selectedValue === 'period') {
                periodFilter.style.display = 'block';
            } else if (selectedValue === 'month') {
                monthFilter.style.display = 'block';
            }
        }

        // Ajoute l'écouteur d'événement
        filterType.addEventListener('change', updateFilters);
        console.log('Écouteur d\'événement ajouté');

        // Applique les filtres au chargement
        updateFilters();
    });
    </script>
    @endpush
@endsection
