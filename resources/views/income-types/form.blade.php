<form action="{{ route('income-types.store') }}" method="POST" class="form">
    @csrf

    <div class="form-group">
        <label for="name" class="form-label">Label</label>
        <input type="text"
               name="name"
               id="name"
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
    </div>
</form>
