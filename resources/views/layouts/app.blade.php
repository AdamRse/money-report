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
            <li><a href="/about">À propos</a></li>
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer>
        Money Report &copy; Adam
    </footer>
</body>
</html>
