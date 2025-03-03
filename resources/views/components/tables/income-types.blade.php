<div class="table-container">
    <table class="revenus-table">
        <thead>
            <tr>
                <th>Label</th>
                <th>Description</th>
                <th>Taxable</th>
                <th>Déclarable</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incomeTypes as $type)
                <tr>
                    <td>{{ $type->name }}</td>
                    <td>{{ $type->description ?: '-' }}</td>
                    <td class="{{ $type->taxable ? 'affirmative' : '' }}">
                        {{ $type->taxable ? "Oui" : "Non" }}
                    </td>
                    <td class="{{ $type->must_declare ? 'affirmative' : '' }}">
                        {{ $type->must_declare ? "Oui" : "Non" }}
                    </td>
                    <td class="actions-cell">
                        <button
                            class="btn btn-secondary btn-edit"
                            onclick="showEditForm('{{ $type->id }}')"
                            data-edit-id="{{ $type->id }}"
                        >
                            Modifier
                        </button>
                        <form
                            action="{{ route('income-types.destroy', $type->id) }}"
                            method="POST"
                            class="inline-form"
                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce type de revenu ?');"
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
