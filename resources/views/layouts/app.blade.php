<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Accueil</a></li>
            @guest
                <li><a href="/login">Connexion</a></li>
                <li><a href="/register">Inscription</a></li>
            @endguest
            @auth
                <li><a href="/revenus">Liste des revenus</a></li>
                <li><a href="/logout">Déconnexion</a></li>
            @endauth
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer>
        Money Report &copy; Adam
    </footer>
    @stack('scripts')
</body>
</html>
