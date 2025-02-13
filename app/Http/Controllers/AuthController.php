<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller {
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): View {
        return view('login');
    }

    /**
     * Gère la tentative de connexion
     */
    public function login(LoginRequest $request): RedirectResponse {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('revenu');
        }

        return back()
            ->withErrors(['email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.'])
            ->onlyInput('email');
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegistrationForm(): View {
        return view('register');
    }

    /**
     * Gère l'inscription d'un nouvel utilisateur
     */
    public function register(RegisterRequest $request): RedirectResponse {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['user'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()
            ->route('revenu')
            ->with('success', 'Inscription réussie');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(Request $request): RedirectResponse {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
