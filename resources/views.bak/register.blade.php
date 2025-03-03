@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="form-container">
    <h1>Inscription</h1>

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

    <form method="POST" action="{{ route('register.request') }}" class="form">
        @csrf

        <div class="form-group">
            <label for="user" class="form-label">Nom d'utilisateur</label>
            <input type="text"
                   id="user"
                   name="user"
                   value="{{ old('user') }}"
                   required
                   class="form-input @error('user') form-input-error @enderror">
            @error('user')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   class="form-input @error('email') form-input-error @enderror">
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password"
                   id="password"
                   name="password"
                   required
                   class="form-input @error('password') form-input-error @enderror">
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input type="password"
                   id="password_confirmation"
                   name="password_confirmation"
                   required
                   class="form-input @error('password_confirmation') form-input-error @enderror">
            @error('password_confirmation')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Créer un compte</button>
            <a href="{{ route('login') }}" class="btn btn-secondary">J'ai déjà un compte</a>
        </div>
    </form>
</div>
@endsection
