<nav>
    <ul>
        @guest
            <li><a href="{{ route('login') }}">Connexion</a></li>
            <li><a href="{{ route('register') }}">Inscription</a></li>
        @endguest
        @auth
            <li><span>{{ auth()->user()->name }}</span></li>
            <li><a href="{{ route('incomes.report') }}">Récapitulatif</a></li>
            <li><a href="{{ route('incomes.index') }}">Revenus</a></li>
            <li><a href="{{ route('incomes.import') }}">Parser un document</a></li>
            <li><a href="{{ route('income-types.index') }}">Types de revenu</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Déconnexion</button>
                </form>
            </li>
        @endauth
    </ul>
</nav>
