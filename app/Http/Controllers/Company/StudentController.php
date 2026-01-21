<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreStudentRequest, UpdateStudentRequest};
use App\Models\{GradeLevel, School, State, Student, StudentEnrollment};
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::query()
            ->with(['currentEnrollment.school.city'])
            ->latest('id')
            ->paginate(20);

        return view('company.students.index', compact('students'));
    }

    public function create()
    {
        // Ajuste: 'order' -> 'sequence'
        $gradeLevels = GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id');
        $states = State::orderBy('name')->pluck('name', 'id');

        return view('company.students.create', compact('gradeLevels', 'states'));
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        $s = $data['student'];
        $e = $data['enrollment'];

        DB::transaction(function () use ($s, $e) {
            // 1) Student (reaproveita por CPF se existir)
            $student = Student::query()
                ->when(! empty($s['cpf']), fn ($q) => $q->where('cpf', $s['cpf']))
                ->first() ?? new Student;

            $student->fill([
                'name' => $s['name'],
                'social_name' => $s['social_name'] ?? null,
                'cpf' => $s['cpf'] ?? null,
                'email' => $s['email'] ?? null,
                'birthdate' => $s['birthdate'] ?? null,
                'race_color' => $s['race_color'] ?? null,
                'has_disability' => (bool) ($s['has_disability'] ?? false),
                'disability_types' => $s['disability_type_ids'] ?? null,
                'disability_details' => $s['disability_details'] ?? null,
                'allergies' => $s['allergies'] ?? null,
                'emergency_contact_name' => $s['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $s['emergency_contact_phone'] ?? null,
            ])->save();

            // 2) DESTINO
            $destSchool = School::findOrFail($e['destination_school_id']);

            // 3) ORIGEM (dependente do escopo)
            $originSchoolId = $this->resolveOriginSchoolId(
                $e['transfer_scope'],
                $e,
                $destSchool
            );

            // 4) Episódio inicial
            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $destSchool->id,
                'grade_level_id' => $e['grade_level_id'],
                'academic_year' => $e['academic_year'],
                'shift' => $e['shift'] ?? 'morning',
                'status' => StudentEnrollment::STATUS_ACTIVE,
                'transfer_scope' => $e['transfer_scope'],
                'origin_school_id' => $originSchoolId,
                'started_at' => $e['started_at'] ?? now()->toDateString(),
                'ended_at' => null,
            ]);
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Aluno cadastrado com matrícula criada.');
    }

    public function show(Student $student)
    {
        $student->load([
            'currentEnrollment.school.city',
            'enrollments.school.city',
            'enrollments.originSchool',
            'enrollments.gradeLevel',
        ]);

        return view('company.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('company.students.edit', compact('student'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $s = $request->validated()['student'];

        $student->fill([
            'name' => $s['name'],
            'social_name' => $s['social_name'] ?? null,
            'cpf' => $s['cpf'] ?? null,
            'email' => $s['email'] ?? null,
            'birthdate' => $s['birthdate'] ?? null,
            'race_color' => $s['race_color'] ?? null,
            'has_disability' => (bool) ($s['has_disability'] ?? false),
            'disability_types' => $s['disability_type_ids'] ?? null,
            'disability_details' => $s['disability_details'] ?? null,
            'allergies' => $s['allergies'] ?? null,
            'emergency_contact_name' => $s['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $s['emergency_contact_phone'] ?? null,
        ])->save();

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Aluno atualizado.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Aluno removido.');
    }
}

