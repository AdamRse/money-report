<form action="{{ route('incomes.import.parse') }}" method="POST" enctype="multipart/form-data" class="form">
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
