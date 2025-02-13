@extends('layouts.app')

@section('title', 'Gestion des types de revenus')

@section('content')
    <div class="form-container">
        <h1>Gestion des types de revenus</h1>

        @if(session('success'))
            <div class="alert alert-success">
                <div class="alert-icon">
                    @include('components.icons.success')
                </div>
                <div class="alert-message">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <div class="alert-icon">
                    <svg viewBox="0 0 24 24" class="alert-icon-svg">
                        <circle cx="12" cy="12" r="11" fill="#DC3545"/>
                        <path d="M12 7v6m0 4h.01" stroke="white" stroke-width="2" fill="none"/>
                    </svg>
                </div>
                <div class="alert-message">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <div class="form-section">
            <h2>Ajouter un type de revenu</h2>
            <form action="{{ route('income_types.store') }}" method="POST" class="form">
                @csrf

                <div class="form-group">
                    <label for="nom" class="form-label">Label</label>
                    <input type="text"
                           name="nom"
                           id="nom"
                           required
                           maxlength="63"
                           minlength="2"
                           value="{{ old('name') }}"
                           class="form-input @error('name') form-input-error @enderror">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description (optionnel)</label>
                    <textarea name="description"
                              id="description"
                              class="form-textarea @error('description') form-input-error @enderror"
                              rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox"
                               name="taxable"
                               id="taxable"
                               value="1"
                               {{ old('taxable') ? 'checked' : '' }}>
                        <label for="taxable" class="form-label">Revenu taxable</label>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox"
                               name="must_declare"
                               id="must_declare"
                               value="1"
                               {{ old('must_declare') ? 'checked' : '' }}>
                        <label for="must_declare" class="form-label">Revenu à déclarer (caf, pole emploi)</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('accueil') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <hr>

        <div class="revenus-card">
            <h2>Types de revenus existants</h2>
            <div class="table-container">
                <table class="revenus-table">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Description</th>
                            <th>taxable</th>
                            <th>Déclarable</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomeTypes as $type)
                            <tr>
                                <td>{{ $type->nom }}</td>
                                <td>{{ $type->description ?: '-' }}</td>
                                <td class="{{ $type->taxable ? 'affirmative' : '' }}">
                                    {{ $type->taxable ? "Oui" : "Non" }}
                                </td>
                                <td class="{{ $type->must_declare ? 'affirmative' : '' }}">
                                    {{ $type->must_declare ? "Oui" : "Non" }}
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-secondary btn-edit" onclick="showEditForm('{{ $type->id }}')">
                                        Modifier
                                    </button>
                                    <form action="{{ route('income_types.destroy', $type->id) }}" method="POST" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type de revenu ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de modification -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Modifier le type de revenu</h2>
            <form id="editForm" action="{{ route('income_types.update', '') }}" method="POST" class="form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="edit_nom" class="form-label">Label</label>
                    <input type="text"
                           name="nom"
                           id="edit_nom"
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

            // Cette fonction sera implémentée plus tard pour charger les données
            document.getElementById('editModal').style.display = 'flex';
        }
        </script>
    @endpush
@endsection
