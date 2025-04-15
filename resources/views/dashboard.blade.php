@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Tableau de bord</h1>
        <p class="welcome-message">Bienvenue, {{ auth()->user()->name }}</p>
    </header>

    <div class="dashboard-grid">
        <!-- Carte de statistiques rapides -->
        <section class="dashboard-card stats-overview">
            <h2>Aperçu financier</h2>
            <div class="stats-quick-view">
                <div class="stat-item">
                    <span class="stat-label">Revenus ce mois</span>
                    <span class="stat-value">{{ number_format($currentMonthTotal, 2, ',', ' ') }} €</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Revenus cette année</span>
                    <span class="stat-value">{{ number_format($currentYearTotal, 2, ',', ' ') }} €</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">À déclarer (année)</span>
                    <span class="stat-value">{{ number_format($yearlyTaxableTotal, 2, ',', ' ') }} €</span>
                </div>
            </div>
            <a href="{{ route('incomes.report') }}" class="btn btn-primary btn-sm view-all-btn">Voir le rapport complet</a>
        </section>

        <!-- Actions rapides -->
        <section class="dashboard-card quick-actions">
            <h2>Actions rapides</h2>
            <div class="actions-grid">
                <a href="{{ route('incomes.index') }}" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M3 3h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h16V5H4zm4.5 9H14a.5.5 0 1 0 0-1h-5.5a.5.5 0 1 0 0 1zm0 2H14a.5.5 0 1 0 0-1h-5.5a.5.5 0 1 0 0 1zm0-4H14a.5.5 0 1 0 0-1h-5.5a.5.5 0 1 0 0 1zm0-2H14a.5.5 0 1 0 0-1h-5.5a.5.5 0 0 0 0 1z" fill="currentColor"/></svg>
                    </div>
                    <h3>Gérer mes revenus</h3>
                    <p>Ajouter, modifier ou supprimer des revenus</p>
                </a>
                <a href="{{ route('incomes.import') }}" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 2a1 1 0 0 1 1 1v3a1 1 0 0 1-2 0V3a1 1 0 0 1 1-1zm0 15a1 1 0 0 1 1 1v3a1 1 0 0 1-2 0v-3a1 1 0 0 1 1-1zm8.66-10a1 1 0 0 1-.366 1.366l-2.598 1.5a1 1 0 1 1-1-1.732l2.598-1.5A1 1 0 0 1 20.66 7zM7.67 14.5a1 1 0 0 1-.366 1.366l-2.598 1.5a1 1 0 1 1-1-1.732l2.598-1.5a1 1 0 0 1 1.366.366zM20.66 17a1 1 0 0 1-1.366.366l-2.598-1.5a1 1 0 0 1 1-1.732l2.598 1.5A1 1 0 0 1 20.66 17zM7.67 9.5a1 1 0 0 1-1.366.366l-2.598-1.5a1 1 0 1 1 1-1.732l2.598 1.5A1 1 0 0 1 7.67 9.5z" fill="currentColor"/></svg>
                    </div>
                    <h3>Importer des données</h3>
                    <p>Analyser et importer des données bancaires</p>
                </a>
                <a href="{{ route('income-types.index') }}" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M20 22H4v-2a5 5 0 0 1 5-5h6a5 5 0 0 1 5 5v2zm-8-9a6 6 0 1 1 0-12 6 6 0 0 1 0 12z" fill="currentColor"/></svg>
                    </div>
                    <h3>Types de revenus</h3>
                    <p>Gérer les catégories de revenus</p>
                </a>
                <a href="{{ route('incomes.report') }}" class="action-card">
                    <div class="action-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="none" d="M0 0h24v24H0z"/><path d="M5 3v16h16v2H3V3h2zm15.293 3.293l1.414 1.414L16 13.414l-3-2.999-4.293 4.292-1.414-1.414L13 7.586l3 2.999 4.293-4.292z" fill="currentColor"/></svg>
                    </div>
                    <h3>Rapport financier</h3>
                    <p>Consulter les statistiques détaillées</p>
                </a>
            </div>
        </section>

        <!-- Derniers revenus -->
        <section class="dashboard-card recent-incomes">
            <div class="card-header">
                <h2>Revenus récents</h2>
                <a href="{{ route('incomes.index') }}" class="btn btn-link">Voir tous</a>
            </div>
            @if($recentIncomes->isEmpty())
                <div class="empty-state">
                    <p>Aucun revenu récent</p>
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
                            @foreach($recentIncomes as $income)
                                <tr>
                                    <td class="date-cell">
                                        {{ Carbon\Carbon::parse($income->income_date)->format('d/m/Y') }}
                                    </td>
                                    <td>{{ $income->incomeType->name }}</td>
                                    <td class="amount-cell">{{ number_format($income->amount, 2, ',', ' ') }} €</td>
                                    <td class="notes-cell">{{ $income->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.dashboard-header {
    margin-bottom: 2rem;
}

.welcome-message {
    color: var(--gray-dark);
    margin-top: 0.5rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.stats-overview,
.recent-incomes {
    grid-column: span 2;
}

.stats-quick-view {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin: 1rem 0;
}

.stat-item {
    flex: 1;
    min-width: 200px;
    background: var(--gray-light);
    padding: 1rem;
    border-radius: var(--radius);
    margin: 0.5rem;
    text-align: center;
}

.stat-label {
    display: block;
    color: var(--gray-dark);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--primary-color);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.action-card {
    background: var(--gray-light);
    border-radius: var(--radius);
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s, box-shadow 0.2s;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.action-icon {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.action-card h3 {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.action-card p {
    color: var(--gray-dark);
    font-size: 0.9rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.btn-link {
    background: none;
    padding: 0;
    color: var(--primary-color);
    text-decoration: underline;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
}

.view-all-btn {
    display: inline-block;
    margin-top: 1rem;
}

@media (max-width: 992px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .stats-overview,
    .recent-incomes {
        grid-column: span 1;
    }
}

@media (max-width: 768px) {
    .stats-quick-view {
        flex-direction: column;
    }

    .stat-item {
        margin: 0.5rem 0;
    }

    .actions-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
