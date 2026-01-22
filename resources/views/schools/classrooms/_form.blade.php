{{-- resources/views/schools/classrooms/_form.blade.php --}}

{{-- Mensagens de sucesso/erro (padrão do projeto) --}}
@include('partials.messages')

<div class="card mb-3">
    <div class="card-header">Dados do Grupo</div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Escola</label>
            <div class="form-control-plaintext">
                {{ $school->short_name ?? $school->name }}
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Oficina (contrato)</label>
            <select name="school_workshop_id" class="form-select @error('school_workshop_id') is-invalid @enderror" required>
                <option value="">-- Selecione --</option>
                @foreach(($schoolWorkshops ?? collect()) as $sw)
                    <option value="{{ $sw->id }}" @selected(old('school_workshop_id') == $sw->id)>
                        {{ $sw->workshop?->name ?? '—' }}
                        — {{ optional($sw->starts_at)->format('d/m/Y') }}
                        @if($sw->ends_at) → {{ $sw->ends_at->format('d/m/Y') }} @endif
                        @if(!empty($sw->status)) ({{ $sw->status }}) @endif
                    </option>
                @endforeach
            </select>
            @error('school_workshop_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">
                O nome do grupo é gerado automaticamente (oficina + séries + ano + grupo).
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Turno</label>
                @php $shiftVal = old('shift', ''); @endphp
                <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
                    <option value="">-- Selecione --</option>
                    <option value="morning"   @selected($shiftVal==='morning')>Manhã</option>
                    <option value="afternoon" @selected($shiftVal==='afternoon')>Tarde</option>
                    <option value="evening"   @selected($shiftVal==='evening')>Noite</option>
                </select>
                @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Ano letivo</label>
                <input type="number" name="academic_year"
                       class="form-control @error('academic_year') is-invalid @enderror"
                       value="{{ old('academic_year', $defaultYear ?? date('Y')) }}"
                       min="2000" max="2100" required>
                @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Capacidade (opcional)</label>
                <input type="number" name="capacity_hint"
                       class="form-control @error('capacity_hint') is-invalid @enderror"
                       value="{{ old('capacity_hint') }}"
                       min="0" placeholder="Ex.: 20">
                @error('capacity_hint') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">Anos escolares</div>
    <div class="card-body">
        @php
            $selectedGrades = (array) old('grade_level_ids', []);
        @endphp

        <select name="grade_level_ids[]" class="form-select @error('grade_level_ids') is-invalid @enderror" multiple required>
            @foreach($gradeLevels as $id => $name)
                <option value="{{ $id }}" @selected(in_array((int)$id, array_map('intval', $selectedGrades), true))>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        <div class="form-text">Selecione um ou mais anos escolares.</div>
        @error('grade_level_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
</div>

