<form action="{{ route('incomes.store') }}" method="POST" class="form">
    @csrf

    <div class="form-group">
        <label for="amount" class="form-label">Montant</label>
        <input type="number"
               name="amount"
               id="amount"
               step="0.01"
               min="0"
               required
               value="{{ old('amount') }}"
               class="form-input @error('amount') form-input-error @enderror">
        @error('amount')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="income_date" class="form-label">Date</label>
        <input type="date"
               name="income_date"
               id="income_date"
               required
               value="{{ old('income_date', date('Y-m-d')) }}"
               class="form-input @error('income_date') form-input-error @enderror">
        @error('income_date')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="income_type_id" class="form-label">Type de revenu</label>
        <select id="income_type_id" name="income_type_id"
                required
                class="form-select @error('income_type_id') form-input-error @enderror">
            <option value="">Sélectionner un type</option>
            @foreach($incomeTypes as $type)
                <option value="{{ $type->id }}"
                        {{ old('income_type_id') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
            <option value="0">
                + Autre (ajouter un nouveau type de revenu)
            </option>
        </select>
        @error('income_type_id')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group new_type_name hidden">
        <h3>Ajouter un type revenu</h3>
        <div class="partLabel">
            <label for="new_type_name" class="form-label">Label</label>
            <input type="text" name="new_type_name" id="new_type_name" class="form-input @error('new_type_name') form-input-error @enderror" />
            @error('new_type_name')
                <span class="error-message">{{ $message }}</span>
            @enderror
            <hr>
            <label for="new_type_description" class="form-label">Description (optionel)</label>
            <textarea type="text" name="new_type_description" id="new_type_description" class="form-input" rows="2"></textarea>
            @error('new_type_description')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        <div class="partCheckbox">
            <input type="checkbox" name="taxable" id="taxable" value="1" />
            <label for="taxable" class="form-label">Revenu taxable</label>
            <br/>
            <hr>
            <input type="checkbox" name="must_declare" id="must_declare" value="1" />
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

@push('scripts')
<script>
    const selectNvRvn = document.querySelector("#income_type_id");
    const divNvRvn = document.querySelector(".form-group.new_type_name");
    const inew_type_name = document.querySelector("#new_type_name");

    function checknew_type_name(){
        if(selectNvRvn.value === "0"){
            divNvRvn.classList.remove("hidden");
            inew_type_name.focus();
        }
        else{
            divNvRvn.classList.add("hidden");
        }
    }
    checknew_type_name();
    selectNvRvn.addEventListener('change', checknew_type_name);
</script>
@endpush
