<div class="revenus-card">
    <h2>Revenus détectés</h2>
    @if(empty($incomes))
        <div class="empty-state">
            <p>Aucun revenu n'a été détecté dans le fichier</p>
        </div>
    @else
        <form action="{{ route('incomes.import.process') }}" method="POST" id="importForm">
            @csrf
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="toggleAll()">Tout sélectionner</button>
                <button type="submit" class="btn btn-primary">Importer les revenus sélectionnés</button>
            </div>

            <div class="table-container">
                <table class="revenus-table">
                    <thead>
                        <tr>
                            <th>Importer</th>
                            <th>Date</th>
                            <th>Libellé</th>
                            <th>Montant</th>
                            <th>Type de revenu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($incomes as $index => $income)
                            <tr>
                                <td>
                                    <input type="checkbox"
                                        name="incomes[{{ $index }}][selected]"
                                        class="import-checkbox"
                                        {{ $income['selected'] ? 'checked' : '' }}>
                                    <input type="hidden"
                                        name="incomes[{{ $index }}][date]"
                                        value="{{ $income['date'] }}">
                                    <input type="hidden"
                                        name="incomes[{{ $index }}][description]"
                                        value="{{ $income['description'] }}">
                                    <input type="hidden"
                                        name="incomes[{{ $index }}][amount]"
                                        value="{{ $income['amount'] }}">
                                </td>
                                <td class="date-cell">{{ $income['date'] }}</td>
                                <td>{{ $income['description'] }}</td>
                                <td class="amount-cell">{{ number_format($income['amount'], 2, ',', ' ') }} €</td>
                                <td>
                                    <select name="incomes[{{ $index }}][income_type_id]"
                                            class="form-select type-select">
                                        <option value="">Sélectionner un type</option>
                                        @foreach($incomeTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ isset($income['income_type_id']) && $income['income_type_id'] == $type->id ? 'selected' : '' }}
                                                title="{{ $type->description }}">
                                                    {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>

        @push('scripts')
        <script>
            function toggleAll() {
                const checkboxes = document.querySelectorAll('.import-checkbox');
                const isAllChecked = [...checkboxes].every(cb => cb.checked);
                const toggleButton = document.querySelector('[onclick="toggleAll()"]');

                checkboxes.forEach(cb => cb.checked = !isAllChecked);

                // Mise à jour du texte du bouton
                toggleButton.textContent = isAllChecked ? 'Tout sélectionner' : 'Tout désélectionner';
            }

            // Validation du formulaire avant envoi
            document.getElementById('importForm').addEventListener('submit', function(e) {
                // Récupérer toutes les lignes cochées
                const checkedRows = document.querySelectorAll('.import-checkbox:checked');

                // Vérifier si au moins un revenu est sélectionné
                if (checkedRows.length === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins un revenu à importer.');
                    return;
                }

                // Vérifier chaque ligne cochée
                let hasError = false;
                checkedRows.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const select = row.querySelector('.type-select');

                    if (!select.value) {
                        hasError = true;
                        select.style.borderColor = '#DC3545';  // Rouge pour indiquer l'erreur
                        select.style.backgroundColor = '#fff5f5';  // Fond légèrement rouge
                    } else {
                        select.style.borderColor = '';  // Réinitialiser le style
                        select.style.backgroundColor = '';
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    alert('Veuillez sélectionner un type de revenu pour tous les revenus cochés.');
                }
            });

            // Réinitialiser le style des selects quand on change leur valeur
            document.querySelectorAll('.type-select').forEach(select => {
                select.addEventListener('change', function() {
                    this.style.borderColor = '';
                    this.style.backgroundColor = '';
                });
            });
        </script>
        @endpush
    @endif
</div>
