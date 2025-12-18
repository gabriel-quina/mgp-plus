@extends('layouts.app')

@push('styles')
    <style>
        :root {
            --brand-navy: #214A6F;
            --brand-navy-700: #193956;
            --brand-yellow: #FDB92E;
            --auth-bg: #F5F8FB;
        }

        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top, rgba(33, 74, 111, .14), transparent 55%),
                var(--auth-bg);
            padding: 24px 12px;
        }

        .auth-wrap {
            width: 100%;
            max-width: 420px;
        }

        .auth-logo {
            max-width: 240px;
            height: auto;
        }

        .auth-card {
            border: 0;
            border-top: 4px solid var(--brand-yellow);
            border-radius: 12px;
        }

        .btn-brand {
            --bs-btn-color: #fff;
            --bs-btn-bg: var(--brand-navy);
            --bs-btn-border-color: var(--brand-navy);

            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: var(--brand-navy-700);
            --bs-btn-hover-border-color: var(--brand-navy-700);

            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: var(--brand-navy-700);
            --bs-btn-active-border-color: var(--brand-navy-700);
        }

        .btn-brand:hover {
            background: var(--brand-navy-700);
            border-color: var(--brand-navy-700);
        }

        .form-control:focus {
            border-color: var(--brand-yellow);
            box-shadow: 0 0 0 .2rem rgba(253, 185, 46, .25);
        }

        a.auth-link {
            color: var(--brand-navy);
        }

        a.auth-link:hover {
            color: var(--brand-navy-700);
        }
    </style>
@endpush

@section('content')
    <div class="auth-page">
        <div class="auth-wrap">
            @include('partials.auth.brand')

            <div class="card shadow-sm auth-card">
                <div class="card-body p-4">
                    @yield('auth-content')
                </div>
            </div>
        </div>
    </div>
@endsection
