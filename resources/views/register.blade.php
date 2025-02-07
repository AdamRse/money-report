@extends('layouts.app')

@section('title', 'Register')

@section('content')
<h1>Inscription</h1>
<form method="POST" action="{{ route('register.request') }}">
    @csrf
    <div>
        <label for="user">Nom d'utilisateur</label>
        <input type="user" id="user" name="user" value="{{ old('user') }}" required>
        @error('user')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
        @error('password')
            <span>{{ $message }}</span>
        @enderror
        <input type="password" id="password_confirmation" name="password_confirmation" required>
        @error('password_confirmation')
            <span>{{ $message }}</span>
        @enderror
    </div>

    <button type="submit">Créer un compte</button>
</form>
@endsection
