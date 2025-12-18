<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Sistema Escolar')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap 5 (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Ícones Bootstrap (opcional) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @stack('styles')
</head>

<body>
    @auth
        @php
            $user = auth()->user();

            // Equipe empresa + Master
            $canSeeMasterNav =
                $user->is_master ||
                $user->hasRole('company_coordinator') ||
                $user->hasRole('company_consultant') ||
                !empty($user->role); // fallback opcional caso você use users.role
        @endphp
        @include('partials.navbar-account')
    @endauth

    <main class="container py-4">
        @yield('content')
    </main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>
