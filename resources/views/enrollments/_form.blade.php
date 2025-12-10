@include('partials.messages')

@php
  $val = fn($field, $fallback = null) =>
      old($field, isset($enrollment) ? $enrollment->{$field} : $fallback);
@endphp

<div class="card mb-3">
  <div class="card-header">Dados da Matrícula</div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Aluno</label>
        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
          <option value="">-- Selecione --</option>
          @foreach($students as $id => $name)
            <option value="{{ $id }}" @selected($val('student_id') == $id)>{{ $name }}</option>
          @endforeach
        </select>
        @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Escola</label>
        <select name="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
          <option value="">-- Selecione --</option>
          @foreach($schools as $id => $name)
            <option value="{{ $id }}" @selected($val('school_id') == $id)>{{ $name }}</option>
          @endforeach
        </select>
        @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Ano escolar</label>
        <select name="grade_level_id" class="form-select @error('grade_level_id') is-invalid @enderror" required>
          <option value="">-- Selecione --</option>
          @foreach($gradeLevels as $id => $name)
            <option value="{{ $id }}" @selected($val('grade_level_id') == $id)>{{ $name }}</option>
          @endforeach
        </select>
        @error('grade_level_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Ano letivo</label>
        <input type="number" name="academic_year"
               class="form-control @error('academic_year') is-invalid @enderror"
               value="{{ old('academic_year', $val('academic_year', $defaultYear ?? date('Y'))) }}"
               min="2000" max="2100" required>
        @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label">Turno</label>
        @php $shiftVal = old('shift', $val('shift', 'morning')); @endphp
        <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
          <option value="morning"   @selected($shiftVal==='morning')>Manhã</option>
          <option value="afternoon" @selected($shiftVal==='afternoon')>Tarde</option>
          <option value="evening"   @selected($shiftVal==='evening')>Noite</option>
        </select>
        @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        @php $statusVal = old('status', $val('status', 'active')); @endphp
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
          <option value="active"      @selected($statusVal==='active')>Cursando</option>
          <option value="completed"   @selected($statusVal==='completed')>Concluída</option>
          <option value="failed"      @selected($statusVal==='failed')>Reprovado</option>
          <option value="transferred" @selected($statusVal==='transferred')>Transferido</option>
          <option value="dropped"     @selected($statusVal==='dropped')>Evasão/Cancelada</option>
          <option value="suspended"   @selected($statusVal==='suspended')>Trancada</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>
  </div>

  <div class="card-footer d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
    <a href="{{ route('student-years.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</div>

