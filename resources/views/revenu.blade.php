@extends('layouts.app')

@section('title', 'Gérer les revenus')

@section('content')
    <div class="form-container">
        <h1>Ajouter un revenu</h1>

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

        <form action="{{ route('revenu.store') }}" method="POST" class="form">
            @csrf

            <div class="form-group">
                <label for="montant" class="form-label">Montant</label>
                <input type="number"
                       name="montant"
                       id="montant"
                       step="0.01"
                       min="0"
                       required
                       value="{{ old('montant') }}"
                       class="form-input @error('montant') form-input-error @enderror">
                @error('montant')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="date_revenu" class="form-label">Date</label>
                <input type="date"
                       name="date_revenu"
                       id="date_revenu"
                       required
                       value="{{ old('date_revenu', date('Y-m-d')) }}"
                       class="form-input @error('date_revenu') form-input-error @enderror">
                @error('date_revenu')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="type_revenu_id" class="form-label">Type de revenu</label>
                <select id="select_type_revenu" name="type_revenu_id"
                        id="type_revenu_id"
                        required
                        class="form-select @error('type_revenu_id') form-input-error @enderror">
                    <option value="">Sélectionner un type</option>
                    @foreach($incomeTypes as $type)
                        <option value="{{ $type->id }}"
                                {{ old('type_revenu_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->nom }}
                        </option>
                    @endforeach
                    <option value="0">
                        + Autre (ajouter un nouveau type de revenu)
                    </option>
                </select>
                @error('type_revenu_id')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group nvRevenu hidden">
                <h3>Ajouter un type revenu</h3>
                <div class="partLabel">
                    <label for="nvRevenu" class="form-label">Label</label>
                    <input type="text" name="nvRevenu" id="nvRevenu" class="form-input @error('nv_revenu') form-input-error @enderror" />
                    @error('nv_revenu')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <hr>
                    <label for="nvRevenuDesc" class="form-label">Description (optionel)</label>
                    <textarea type="text" name="nvRevenuDesc" id="nvRevenuDesc" class="form-input" rows="2"></textarea>
                    @error('nv_revenu')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="partCheckbox">
                    <input type="checkbox" name="taxable" id="taxable" checked="1" value="1" />
                    <label for="taxable" class="form-label">Revenu taxable</label>
                    <br/>
                    <hr>
                    <input type="checkbox" name="must_declare" id="must_declare" checked="1" value="1" />
                    <label for="must_declare" class="form-label">Revenu à déclarer (caf, pole emploi)</label>
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes (optionnel)</label>
                <textarea name="notes"
                          id="notes"
                          class="form-textarea @error('notes') form-input-error @enderror"
                          rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>

    <!-- Liste des revenus -->
    <div class="revenus-card">
        <div class="revenus-header">
            <h2>Liste des revenus</h2>
            <form method="GET" action="{{ route('revenu') }}" class="form-group">
                <select name="annee_filtre" id="annee_filtre" class="form-select" onchange="this.form.submit()">
                    @foreach(range(date('Y'), date('Y')-5) as $annee)
                        <option value="{{ $annee }}"
                                {{ $selectedYear == $annee ? 'selected' : '' }}>
                            {{ $annee }}
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
            <div class="table-container">
                <table class="revenus-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Montant</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomes as $income)
                            <tr data-type-id="{{ $income->type_revenu_id }}">
                                <td class="date-cell">
                                    {{ Carbon\Carbon::parse($income->date_revenu)->format('d/m/Y') }}
                                </td>
                                <td>{{ $income->typeRevenu->nom }}</td>
                                <td class="amount-cell">{{ number_format($income->montant, 2, ',', ' ') }} €</td>
                                <td class="notes-cell">{{ $income->notes ?: '-' }}</td>
                                <td class="actions-cell">
                                    <button
                                        class="btn btn-secondary btn-edit"
                                        onclick="showEditForm('{{ $income->id }}')"
                                    >
                                        Modifier
                                    </button>
                                    <form
                                        action="{{ route('revenu.destroy', $income->id) }}"
                                        method="POST"
                                        class="inline-form"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce revenu ?');"
                                    >
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
        @endif
    </div>

    <!-- Modal de modification -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Modifier le revenu</h2>
            <form id="editForm" action="{{ route('revenu.update', '') }}" method="POST" class="form">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="edit_montant" class="form-label">Montant</label>
                    <input type="number"
                           name="montant"
                           id="edit_montant"
                           step="0.01"
                           min="0"
                           required
                           class="form-input">
                </div>

                <div class="form-group">
                    <label for="edit_date_revenu" class="form-label">Date</label>
                    <input type="date"
                           name="date_revenu"
                           id="edit_date_revenu"
                           required
                           class="form-input">
                </div>

                <div class="form-group">
                    <label for="edit_type_revenu_id" class="form-label">Type de revenu</label>
                    <select name="type_revenu_id"
                            id="edit_type_revenu_id"
                            required
                            class="form-select">
                        @foreach($incomeTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->nom }}</option>
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
        const selectNvRvn = document.querySelector("#select_type_revenu");
        const divNvRvn = document.querySelector(".form-group.nvRevenu");
        const iNvRevenu = document.querySelector("#nvRevenu");

        function checkNvrevenu(){
            if(selectNvRvn.value === "0"){
                divNvRvn.classList.remove("hidden");
                iNvRevenu.focus();
            }
            else{
                divNvRvn.classList.add("hidden");
            }
        }
        checkNvrevenu();
        selectNvRvn.addEventListener('change', checkNvrevenu);

        // Fonctions pour le modal d'édition
        function hideEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }

        function showEditForm(revenuId) {
            // Récupérer les données du revenu dans le tableau
            const row = document.querySelector(`button[onclick="showEditForm('${revenuId}')"]`).closest('tr');

            // Récupérer les valeurs
            const date = row.querySelector('.date-cell').textContent.trim().split('/').reverse().join('-');
            const montant = row.querySelector('.amount-cell').textContent.trim().replace(' €', '').replace(',', '.').trim();
            const notes = row.querySelector('.notes-cell').textContent.trim();
            const notes_content = notes === '-' ? '' : notes;
            const type_id = row.getAttribute('data-type-id');

            // Remplir le formulaire
            document.getElementById('edit_montant').value = montant;
            document.getElementById('edit_date_revenu').value = date;
            document.getElementById('edit_type_revenu_id').value = type_id;
            document.getElementById('edit_notes').value = notes_content;

            // Mettre à jour l'URL du formulaire
            const form = document.getElementById('editForm');
            form.action = form.action + '/' + revenuId;

            // Afficher le modal
            document.getElementById('editModal').style.display = 'flex';
        }

        // Gestion du filtre par année
        document.getElementById('annee_filtre').addEventListener('change', function() {
            // Soumettre le formulaire avec l'année sélectionnée
            this.form.submit();
        });
    </script>
    @endpush
@endsection
