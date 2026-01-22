<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolWorkshop;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolClassroomController extends Controller
{
    public function index(Request $request, School $school)
    {
        $q  = (string) $request->query('q', '');
        $yr = $request->query('year');
        $sh = $request->query('shift');

        $query = Classroom::query()
            ->where('school_id', $school->id)
            ->with([
                'gradeLevels',
                'schoolWorkshop.workshop',
            ]);

        if ($q !== '') {
            // Classroom::name é accessor; filtra por nome da oficina do contrato.
            $query->whereHas('schoolWorkshop.workshop', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%");
            });
        }

        if ($yr !== null && $yr !== '') {
            $query->where('academic_year', (int) $yr);
        }

        if ($sh !== null && $sh !== '') {
            $query->where('shift', (string) $sh);
        }

        $classrooms = $query
            ->orderByDesc('academic_year')
            ->orderBy('shift')
            ->orderBy('grades_signature')
            ->orderBy('group_number')
            ->paginate(20)
            ->withQueryString();

        $classrooms->getCollection()->transform(function ($classroom) {
            if (method_exists($classroom, 'eligibleEnrollments')) {
                $classroom->total_all_students = $classroom->eligibleEnrollments()->count();
            }
            return $classroom;
        });

        return view('schools.classrooms.index', compact('school', 'classrooms', 'q', 'yr', 'sh'));
    }

    public function show(School $school, Classroom $classroom)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->load([
            'gradeLevels',
            'schoolWorkshop.workshop',
            'school',
        ]);

        return view('schools.classrooms.show', compact('school', 'classroom'));
    }

    public function create(School $school)
    {
        // Somente séries que têm matrículas na escola (distinct grade_level_id)
        $gradeLevelIds = $school->enrollments()
            ->select('grade_level_id')
            ->whereNotNull('grade_level_id')
            ->distinct()
            ->pluck('grade_level_id')
            ->all();

        $gradeLevels = GradeLevel::query()
            ->whereIn('id', $gradeLevelIds)
            ->orderBy('sequence')
            ->orderBy('name')
            ->pluck('name', 'id');

        // Mantido por compatibilidade com telas/helper antigos (se ainda usam)
        $workshops = Workshop::query()
            ->orderBy('name')
            ->get();

        // Contratos ativos hoje
        $schoolWorkshops = $school->schoolWorkshops()
            ->with('workshop')
            ->activeAt()
            ->orderBy('starts_at')
            ->get();

        return view('schools.classrooms.create', [
            'school' => $school,
            'gradeLevels' => $gradeLevels,
            'workshops' => $workshops,
            'schoolWorkshops' => $schoolWorkshops,
            'defaultYear' => (int) date('Y'),
        ]);
    }

    public function store(Request $request, School $school)
    {
        $data = $request->validate([
            'school_workshop_id' => ['required', 'integer'],
            'grade_level_ids'    => ['required', 'array', 'min:1'],
            'grade_level_ids.*'  => ['integer'],
            'academic_year'      => ['required', 'integer', 'min:2000', 'max:2100'],
            'shift'              => ['required', 'string', 'max:50'],
            'capacity_hint'      => ['nullable', 'integer', 'min:0'],
            'status'             => ['nullable', 'string', 'max:50'],
        ]);

        $schoolWorkshop = SchoolWorkshop::query()
            ->whereKey((int) $data['school_workshop_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        $gradeIds = Classroom::normalizeGradeLevelIds($data['grade_level_ids']);
        $gradesSignature = Classroom::buildGradesSignature($data['grade_level_ids']);

        if (empty($gradeIds) || $gradesSignature === '') {
            return back()
                ->withErrors(['grade_level_ids' => 'Selecione ao menos uma série válida.'])
                ->withInput();
        }

        $academicYear = (int) $data['academic_year'];
        $shift = (string) $data['shift'];
        $startsAt = now();

        $classroom = DB::transaction(function () use (
            $school,
            $schoolWorkshop,
            $gradeIds,
            $gradesSignature,
            $academicYear,
            $shift,
            $data,
            $startsAt
        ) {
            $last = Classroom::query()
                ->where('school_id', $school->id)
                ->where('school_workshop_id', $schoolWorkshop->id)
                ->where('grades_signature', $gradesSignature)
                ->where('academic_year', $academicYear)
                ->where('shift', $shift)
                ->orderByDesc('group_number')
                ->lockForUpdate()
                ->first();

            $nextGroupNumber = ((int) ($last?->group_number ?? 0)) + 1;

            $classroom = Classroom::create([
                'school_id'          => $school->id,
                'school_workshop_id' => $schoolWorkshop->id,
                'grades_signature'   => $gradesSignature,
                'group_number'       => $nextGroupNumber,
                'academic_year'      => $academicYear,
                'shift'              => $shift,
                'capacity_hint'      => $data['capacity_hint'] ?? null,
                'status'             => $data['status'] ?? null,
            ]);

            $classroom->gradeLevels()->sync($gradeIds);

            /**
             * Auto-alocação na criação:
             * - pega enrollments elegíveis (school + grade_levels do grupo)
             * - aloca SOMENTE quem NÃO tem membership ativa (evita "mover" alunos automaticamente)
             */
            $eligibleEnrollmentIds = $school->enrollments()
                ->whereIn('grade_level_id', $gradeIds)
                ->pluck('id');

            $alreadyAllocatedIds = ClassroomMembership::query()
                ->activeAt($startsAt)
                ->whereIn('student_enrollment_id', $eligibleEnrollmentIds)
                ->pluck('student_enrollment_id');

            $toAllocateIds = $eligibleEnrollmentIds->diff($alreadyAllocatedIds)->values();

            foreach ($toAllocateIds as $enrollmentId) {
                ClassroomMembership::create([
                    'student_enrollment_id' => (int) $enrollmentId,
                    'classroom_id' => $classroom->id,
                    'starts_at' => $startsAt,
                    'ends_at' => null,
                ]);
            }

            return $classroom;
        });

        return redirect()
            ->route('schools.classrooms.show', [$school, $classroom])
            ->with('success', 'Grupo criado.');
    }
}

