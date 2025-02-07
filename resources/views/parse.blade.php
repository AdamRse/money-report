@extends('layouts.app')

@section('title', 'Parseur de relevés bancaires')

@section('content')
    <div class="form-container">
        <h1>Import de relevé bancaire</h1>

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

        <form action="{{ route('parse.request') }}" method="POST" enctype="multipart/form-data" class="form">
            @csrf

            <div class="form-group">
                <label for="bankFile" class="form-label">Fichier bancaire (CSV ou TSV)</label>
                <input type="file"
                       name="bankFile"
                       id="bankFile"
                       accept=".csv,.tsv,text/csv,text/tab-separated-values"
                       required
                       class="form-input @error('bankFile') form-input-error @enderror">
                <small class="form-text">Formats acceptés : CSV, TSV</small>
                @error('bankFile')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Analyser le fichier</button>
        </form>
    </div>

    @if(isset($revenus))
    <div class="revenus-card">
        <h2>Revenus détectés</h2>
        @if(empty($revenus))
            <div class="empty-state">
                <p>Aucun revenu n'a été détecté dans le fichier</p>
            </div>
        @else
            <form action="{{ route('multipleRevenus') }}" method="POST" id="importForm">
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
                            @foreach($revenus as $index => $revenu)
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                            name="revenus[{{ $index }}][selected]"
                                            class="import-checkbox"
                                            {{ $revenu['selected'] ? 'checked' : '' }}>
                                        <input type="hidden"
                                            name="revenus[{{ $index }}][date]"
                                            value="{{ $revenu['date'] }}">
                                        <input type="hidden"
                                            name="revenus[{{ $index }}][libelle]"
                                            value="{{ $revenu['libelle'] }}">
                                        <input type="hidden"
                                            name="revenus[{{ $index }}][montant]"
                                            value="{{ $revenu['montant'] }}">
                                    </td>
                                    <td class="date-cell">{{ $revenu['date'] }}</td>
                                    <td>{{ $revenu['libelle'] }}</td>
                                    <td class="amount-cell">{{ number_format($revenu['montant'], 2, ',', ' ') }} €</td>
                                    <td>
                                        <select name="revenus[{{ $index }}][type_revenu_id]"
                                                class="form-select type-select">
                                            <option value="">Sélectionner un type</option>
                                            @foreach($typeRevenus as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ isset($revenu['type_revenu_id']) && $revenu['type_revenu_id'] == $type->id ? 'selected' : '' }}
                                                    title="{{ $type->description }}">
                                                        {{ $type->nom }}
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
                    checkboxes.forEach(cb => cb.checked = !isAllChecked);
                }
            </script>
            @endpush
        @endif
    </div>
@endif
@endsection
