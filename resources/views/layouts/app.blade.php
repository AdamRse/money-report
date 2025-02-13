<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <nav>
        <ul>
            @guest
                <li><a href="/login">Connexion</a></li>
                <li><a href="/register">Inscription</a></li>
            @endguest
            @auth
                <li><span>{{ auth()->user()->name }}</span></li>
                <li><a href="/incomes">Récapitulatif</a></li>
                <li><a href="/revenu">incomes</a></li>
                <li><a href="/parse">Parser un document</a></li>
                <li><a href="/type-revenu">Types de revenu</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Déconnexion</button>
                    </form>
                </li>
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
