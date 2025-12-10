@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
  <h3 class="mb-0">Editar Matrícula</h3>
  <div class="d-flex gap-2">
    <a href="{{ route('student-years.index') }}" class="btn btn-outline-secondary">Listar</a>
  </div>
</header>

<form method="POST" action="{{ route('student-years.update', $enrollment) }}">
  @csrf
  @method('PUT')

  @include('student-years._form', [
    'enrollment'  => $enrollment,
    'students'    => $students,
    'schools'     => $schools,
    'gradeLevels' => $gradeLevels,
    'defaultYear' => $defaultYear
  ])
</form>
@endsection

