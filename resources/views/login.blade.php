@extends('layouts.app')

@section('title', 'Login')

@section('content')
<h1>Connexion</h1>
<form method="POST" action="{{ route('login.request') }}">
    @csrf
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
    </div>

    <div>
        <label>
            <input type="checkbox" name="remember">
            Se souvenir de moi
        </label>
    </div>

    <button type="submit">Se connecter</button>
</form>
@endsection
