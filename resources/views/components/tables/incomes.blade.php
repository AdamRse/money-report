<div class="table-container">
    <table class="revenus-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Notes</th>
                @if(isset($showActions) && $showActions)
                    <th>Actions</th>
                @endif
                @if(isset($showTaxInfo) && $showTaxInfo)
                    <th>Taxable</th>
                    <th>Déclarable</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($incomes as $income)
                <tr data-type-id="{{ $income->income_type_id }}">
                    <td class="date-cell">
                        {{ Carbon\Carbon::parse($income->income_date)->format('d/m/Y') }}
                    </td>
                    <td>{{ $income->incomeType->name }}</td>
                    <td class="amount-cell">{{ number_format($income->amount, 2, ',', ' ') }} €</td>
                    <td class="notes-cell">{{ $income->notes ?: '-' }}</td>

                    @if(isset($showActions) && $showActions)
                        <td class="actions-cell">
                            <button
                                class="btn btn-secondary btn-edit"
                                onclick="showEditForm('{{ $income->id }}')"
                            >
                                Modifier
                            </button>
                            <form
                                action="{{ route('incomes.destroy', $income->id) }}"
                                method="POST"
                                class="inline-form"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce revenu ?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </td>
                    @endif

                    @if(isset($showTaxInfo) && $showTaxInfo)
                        <td class="{{ $income->incomeType->taxable ? 'affirmative' : '' }}">
                            {{ $income->incomeType->taxable ? "Oui" : "Non" }}
                        </td>
                        <td class="{{ $income->incomeType->must_declare ? 'affirmative' : '' }}">
                            {{ $income->incomeType->must_declare ? "Oui" : "Non" }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
