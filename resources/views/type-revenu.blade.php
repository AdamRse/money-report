@extends('layouts.app')

@section('title', 'Ajouter un type de revenu')

@section('content')
    <div class="form-container">
        <h1>Ajouter un type de revenu</h1>

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

        <form action="{{ route('typeRevenu.store') }}" method="POST" class="form">
            @csrf

            <div class="form-group">
                <label for="nom" class="form-label">Label</label>
                <input type="text"
                       name="nom"
                       id="nom"
                       required
                       maxlength="63"
                       minlength="2"
                       value="{{ old('nom') }}"
                       class="form-input @error('nom') form-input-error @enderror">
                @error('nom')
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
                           name="imposable"
                           id="imposable"
                           value="1"
                           {{ old('imposable') ? 'checked' : '' }}>
                    <label for="imposable" class="form-label">Revenu imposable</label>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox"
                           name="declarable"
                           id="declarable"
                           value="1"
                           {{ old('declarable') ? 'checked' : '' }}>
                    <label for="declarable" class="form-label">Revenu à déclarer (caf, pole emploi)</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('accueil') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
@endsection
