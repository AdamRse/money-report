@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="form-container">
    <h1>Connexion</h1>

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

    <form method="POST" action="{{ route('login.request') }}" class="form">
        @csrf

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
            <label class="checkbox-group">
                <input type="checkbox" name="remember" class="form-checkbox">
                <span class="form-label">Se souvenir de moi</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Se connecter</button>
            <a href="{{ route('register') }}" class="btn btn-secondary">Créer un compte</a>
        </div>
    </form>
</div>
@endsection
