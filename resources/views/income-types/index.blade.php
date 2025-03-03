@extends('layouts.app')

@section('title', 'Gestion des types de revenus')

@section('content')
    <div class="form-container">
        <h1>Gestion des types de revenus</h1>

        @if(session('success'))
            @include('components.alerts.success', ['message' => session('success')])
        @endif

        @if(session('error'))
            @include('components.alerts.error', ['message' => session('error')])
        @endif

        <div class="form-section">
            <h2>Ajouter un type de revenu</h2>
            @include('income-types.form')
        </div>

        <hr>

        <div class="revenus-card">
            <h2>Types de revenus existants</h2>
            @include('components.tables.income-types')
        </div>
    </div>

    <!-- Modal de modification -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Modifier le type de revenu</h2>
            <form id="editForm" action="{{ route('income-types.update', '') }}" method="POST" class="form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="edit_name" class="form-label">Label</label>
                    <input type="text"
                           name="name"
                           id="edit_name"
                           required
                           maxlength="63"
                           minlength="2"
                           class="form-input">
                </div>

                <div class="form-group">
                    <label for="edit_description" class="form-label">Description (optionnel)</label>
                    <textarea name="description"
                              id="edit_description"
                              class="form-textarea"
                              rows="3"></textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox"
                               name="taxable"
                               id="edit_taxable"
                               value="1">
                        <label for="edit_taxable" class="form-label">Revenu taxable</label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox"
                               name="must_declare"
                               id="edit_must_declare"
                               value="1">
                        <label for="edit_must_declare" class="form-label">Revenu à déclarer</label>
                    </div>
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
        function hideEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }

        function showEditForm(typeId) {
            // Met à jour l'URL du formulaire avec l'ID
            const form = document.getElementById('editForm');
            form.action = form.action + '/' + typeId;

            // Récupérer la ligne correspondante dans le tableau
            const row = document.querySelector(`[data-edit-id="${typeId}"]`).closest('tr');

            // Extraire les valeurs
            const name = row.querySelector('td:nth-child(1)').textContent.trim();
            const description = row.querySelector('td:nth-child(2)').textContent.trim();
            const description_content = description === '-' ? '' : description;
            const taxable = row.querySelector('td:nth-child(3)').textContent.trim() === 'Oui';
            const must_declare = row.querySelector('td:nth-child(4)').textContent.trim() === 'Oui';

            // Remplir le formulaire
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description_content;
            document.getElementById('edit_taxable').checked = taxable;
            document.getElementById('edit_must_declare').checked = must_declare;

            // Afficher le modal
            document.getElementById('editModal').style.display = 'flex';
        }
    </script>
    @endpush
@endsection
