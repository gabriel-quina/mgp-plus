@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
  <h3 class="mb-0">Nova Matrícula</h3>
  <div class="d-flex gap-2">
    <a href="{{ route('student-years.index') }}" class="btn btn-outline-secondary">Voltar</a>
  </div>
</header>

<form method="POST" action="{{ route('student-years.store') }}">
  @csrf

  @include('student-years._form', [
    'students'    => $students,
    'schools'     => $schools,
    'gradeLevels' => $gradeLevels,
    'defaultYear' => $defaultYear
  ])
</form>
@endsection

