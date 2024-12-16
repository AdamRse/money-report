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
            <li><a href="/revenus">Liste des revenus</a></li>
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
