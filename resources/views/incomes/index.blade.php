@extends('layouts.app')

@section('title', 'Gérer les revenus')

@section('content')
    <div class="form-container">
        <h1>Ajouter un revenu</h1>

        @if(session('success'))
            @include('components.alerts.success', ['message' => session('success')])
        @endif

        @if(session('error'))
            @include('components.alerts.error', ['message' => session('error')])
        @endif

        @include('incomes.form')
    </div>

    <!-- Liste des revenus -->
    <div class="revenus-card">
        <div class="revenus-header">
            <h2>Liste des revenus</h2>
            <form method="GET" action="{{ route('incomes.index') }}" class="form-group">
                <select name="year_filter" id="year_filter" class="form-select" onchange="this.form.submit()">
                    @foreach(range(date('Y'), date('Y')-5) as $year)
                        <option value="{{ $year }}"
                                {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($incomes->isEmpty())
            <div class="empty-state">
                <p>Aucun revenu enregistré pour cette année</p>
            </div>
        @else
            @include('components.tables.incomes', ['showActions' => true])
        @endif
    </div>

    <!-- Modal de modification -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Modifier le revenu</h2>
            <form id="editForm" action="{{ route('incomes.update', '') }}" method="POST" class="form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="edit_amount" class="form-label">Montant</label>
                    <input type="number"
                           name="amount"
                           id="edit_amount"
                           step="0.01"
                           min="0"
                           required
                           class="form-input">
                </div>

                <div class="form-group">
                    <label for="edit_income_date" class="form-label">Date</label>
                    <input type="date"
                           name="income_date"
                           id="edit_income_date"
                           required
                           class="form-input">
                </div>

                <div class="form-group">
                    <label for="edit_income_type_id" class="form-label">Type de revenu</label>
                    <select name="income_type_id"
                            id="edit_income_type_id"
                            required
                            class="form-select">
                        @foreach($incomeTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_notes" class="form-label">Notes (optionnel)</label>
                    <textarea name="notes"
                             id="edit_notes"
                             class="form-textarea"
                             rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Fonctions pour le modal d'édition
        function hideEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }

        function showEditForm(revenuId) {
            // Récupérer les données du revenu dans le tableau
            const row = document.querySelector(`button[onclick="showEditForm('${revenuId}')"]`).closest('tr');

            // Récupérer les valeurs
            const date = row.querySelector('.date-cell').textContent.trim().split('/').reverse().join('-');
            const amount = row.querySelector('.amount-cell').textContent.trim().replace(' €', '').replace(',', '.').trim();
            const notes = row.querySelector('.notes-cell').textContent.trim();
            const notes_content = notes === '-' ? '' : notes;
            const type_id = row.getAttribute('data-type-id');

            // Remplir le formulaire
            document.getElementById('edit_amount').value = amount;
            document.getElementById('edit_income_date').value = date;
            document.getElementById('edit_income_type_id').value = type_id;
            document.getElementById('edit_notes').value = notes_content;

            // Mettre à jour l'URL du formulaire
            const form = document.getElementById('editForm');
            form.action = form.action + '/' + revenuId;

            // Afficher le modal
            document.getElementById('editModal').style.display = 'flex';
        }

        // Gestion du filtre par année
        document.getElementById('year_filter').addEventListener('change', function() {
            // Soumettre le formulaire avec l'année sélectionnée
            this.form.submit();
        });
    </script>
    @endpush
@endsection
