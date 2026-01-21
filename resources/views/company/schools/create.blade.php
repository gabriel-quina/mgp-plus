@extends('layouts.app')

@section('content')
    <h1 class="h3 mb-3">
        Nova Escola</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.schools.store') }}">
        @csrf
        @include('company.schools._form', ['submitLabel' => 'Criar'])
    </form>
@endsection
