<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-person-circle"></i> Minha Área
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#account-topbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="account-topbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        Dashboard
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">
                    {{ auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark">
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
