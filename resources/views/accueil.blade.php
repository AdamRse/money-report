@extends('layouts.app')

@section('title', 'Ajouter un revenu')

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
                    @foreach($typeRevenus as $type)
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
                    <input type="checkbox" name="imposable" id="imposable" checked="1" value="1" />
                    <label for="imposable" class="form-label">Revenu imposable</label>
                    <br/>
                    <hr>
                    <input type="checkbox" name="declarable" id="declarable" checked="1" value="1" />
                    <label for="declarable" class="form-label">Revenu à déclarer (caf, pole emploi)</label>
                </div>
            </div>
            @push('scripts')
            <script>
                const selectNvRvn = document.querySelector("#select_type_revenu");
                const divNvRvn = document.querySelector(".form-group.nvRevenu");
                const iNvRevenu = document.querySelector("#nvRevenu");

                function checkNvrevenu(){
                    console.log(selectNvRvn.value);

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
            </script>
            @endpush


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
@endsection
