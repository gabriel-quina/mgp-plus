<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\{Classroom, GradeLevel, School, SchoolWorkshop, Workshop};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolClassroomController extends Controller
{
    protected function shiftLabels(): array
    {
        return [
            'morning' => 'Manhã',
            'afternoon' => 'Tarde',
            'evening' => 'Noite',
        ];
    }

    public function index(Request $request, School $school)
    {
        $q = (string) $request->query('q', '');
        $yr = $request->query('year');
        $sh = $request->query('shift');

        $query = Classroom::query()
            ->where('school_id', $school->id)
            ->with([
                'gradeLevels',
                'schoolWorkshop.workshop',
            ]);

        if ($q !== '') {
            // Classroom::name é accessor (não dá pra filtrar direto). Filtra por nome da oficina do contrato.
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

        // Compatível com view que tenta exibir total_all_students (se existir método no model).
        $classrooms->getCollection()->transform(function ($classroom) {
            if (method_exists($classroom, 'eligibleEnrollments')) {
                $classroom->total_all_students = $classroom->eligibleEnrollments()->count();
            }

            return $classroom;
        });

        return view('schools.classrooms.index', [
            'school' => $school,
            'classrooms' => $classrooms,
            'q' => $q,
            'yr' => $yr,
            'sh' => $sh,
            'shiftLabels' => $this->shiftLabels(),
        ]);
    }

    public function show(School $school, Classroom $classroom)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->load([
            'gradeLevels',
            'schoolWorkshop.workshop',
        ]);

        return view('schools.classrooms.show', [
            'school' => $school,
            'classroom' => $classroom,
            'shiftLabels' => $this->shiftLabels(),
        ]);
    }

    public function create(School $school)
    {
        $gradeLevels = GradeLevel::query()
            ->orderBy('sequence')
            ->orderBy('name')
            ->pluck('name', 'id');

        // Mantido para compatibilidade com wizard/helper e/ou form legado
        $workshops = Workshop::query()
            ->orderBy('name')
            ->get();

        // NOVO: contratos escola↔oficina (ativos hoje)
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
            'shiftLabels' => $this->shiftLabels(),
        ]);
    }

    public function store(Request $request, School $school)
    {
        $data = $request->validate([
            // Compatibilidade: form novo usa school_workshop_id; wizard/legado ainda pode mandar workshop_id.
            'school_workshop_id' => ['nullable', 'integer'],
            'workshop_id' => ['nullable', 'integer'],

            'grade_level_ids' => ['required', 'array', 'min:1'],
            'grade_level_ids.*' => ['integer'],

            'academic_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'shift' => ['required', 'string', 'max:50'],

            'capacity_hint' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $schoolWorkshop = null;

        if (! empty($data['school_workshop_id'])) {
            $schoolWorkshop = SchoolWorkshop::query()
                ->whereKey((int) $data['school_workshop_id'])
                ->where('school_id', $school->id)
                ->firstOrFail();
        } elseif (! empty($data['workshop_id'])) {
            // Compatibilidade: resolve contrato ativo da oficina para esta escola.
            $schoolWorkshop = SchoolWorkshop::query()
                ->where('school_id', $school->id)
                ->where('workshop_id', (int) $data['workshop_id'])
                ->activeAt()
                ->orderByDesc('starts_at')
                ->firstOrFail();
        } else {
            return back()
                ->withErrors(['school_workshop_id' => 'Selecione a oficina.'])
                ->withInput();
        }

        $gradeIds = Classroom::normalizeGradeLevelIds($data['grade_level_ids']);
        $gradesSignature = Classroom::buildGradesSignature($data['grade_level_ids']);

        if (empty($gradeIds) || $gradesSignature === '') {
            return back()
                ->withErrors(['grade_level_ids' => 'Selecione ao menos uma série válida.'])
                ->withInput();
        }

        $academicYear = (int) $data['academic_year'];
        $shift = (string) $data['shift'];

        $classroom = DB::transaction(function () use ($school, $schoolWorkshop, $gradeIds, $gradesSignature, $academicYear, $shift, $data) {
            // group_number sequencial por (contrato + assinatura + ano + turno)
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
                'school_id' => $school->id,
                'school_workshop_id' => $schoolWorkshop->id,
                'grades_signature' => $gradesSignature,
                'group_number' => $nextGroupNumber,
                'academic_year' => $academicYear,
                'shift' => $shift,
                'capacity_hint' => $data['capacity_hint'] ?? null,
                'status' => $data['status'] ?? null,
            ]);

            $classroom->gradeLevels()->sync($gradeIds);

            return $classroom;
        });

        return redirect()
            ->route('schools.classrooms.show', [$school, $classroom])
            ->with('success', 'Turma criada.');
    }
}
