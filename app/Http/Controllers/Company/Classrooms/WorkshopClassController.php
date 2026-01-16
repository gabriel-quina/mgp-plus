<?php

namespace App\Http\Controllers\Company\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\{Classroom, Workshop, WorkshopAllocation};

class WorkshopClassController extends Controller
{
    /**
     * Exibe a oficina dentro da TURMA PAI quando ela não usa subturmas.
     *
     * Contexto:
     * - Classroom PAI (parent_classroom_id = null)
     * - Workshop vinculada à turma via pivot (max_students no pivot)
     * - Todos os alunos vêm dos episódios elegíveis da turma PAI
     *
     * Rota:
     * GET /turmas/{classroom}/oficinas/{workshop}
     * name: classrooms.workshops.show
     */
    public function show(Classroom $classroom, Workshop $workshop)
    {
        // Garante que é TURMA PAI
        abort_if($classroom->parent_classroom_id, 404);

        // Carrega relações necessárias + oficinas da turma (para acessar o pivot corretamente)
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        // Oficina vinculada a ESSA turma (com pivot -> max_students)
        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        // Alunos elegíveis da turma PAI para essa oficina (mesma base da PAI)
        $enrollments = $classroom->eligibleEnrollments()
            ->with(['student', 'gradeLevel'])
            ->get()
            ->sortBy(fn ($e) => mb_strtolower(optional($e->student)->name ?? ''));

        // Capacidade no pivot da turma PAI + oficina
        $max = (int) ($workshopForClass->pivot->max_students ?? 0);

        // ✅ Novo: precisamos do school para montar rotas schools.* (pedagógico agora é por escola)
        $schoolId = (int) ($classroom->school_id ?? optional($classroom->school)->id);
        abort_if(! $schoolId, 500); // se isso acontecer, seu Classroom está sem school_id

        return view('classrooms.workshops.group', [
            'pageTitle' => 'Oficina — '.$workshopForClass->name,
            'headerTitle' => 'Oficina — '.$workshopForClass->name,

            'contextLine' => sprintf(
                'Turma: <strong>%s</strong> · Escola: <strong>%s</strong> · Ano letivo: <strong>%d</strong> · Turno: <strong>%s</strong>',
                e($classroom->name),
                e(optional($classroom->school)->name ?? '—'),
                $classroom->academic_year,
                $classroom->shift ?? '—',
            ),

            // Mantém o padrão que você já estava usando: "a que anos essa turma/oficina atende"
            'workshopLine' => 'Atende: '.$classroom->gradeLevels
                ->map(fn ($gl) => '<span class="badge bg-secondary">'.e($gl->short_name ?? $gl->name).'</span>')
                ->implode(' '),

            'studentsLabel' => 'Total de alunos (turma inteira)',
            'studentsCount' => $enrollments->count(),

            'capacity' => $max,
            'capacityLabel' => 'Capacidade máxima da oficina',

            'tableTitle' => 'Alunos da turma',
            'emptyMessage' => 'Nenhum aluno elegível para esta oficina.',

            // Contexto bruto pra view
            'classroom' => $classroom,
            'workshop' => $workshopForClass, // IMPORTANTE: com pivot carregado
            'enrollments' => $enrollments,

            // Voltar → Turma PAI (MASTER)
            'backUrl' => route('classrooms.show', $classroom),

            // ✅ Pedagógico agora é no escopo da escola
            'launchLessonUrl' => route('schools.lessons.create', [
                'school' => $schoolId,
                'classroom' => $classroom->id,
                'workshop' => $workshopForClass->id,
            ]),

            'lessonsIndexUrl' => route('schools.lessons.index', [
                'school' => $schoolId,
                'classroom' => $classroom->id,
                'workshop' => $workshopForClass->id,
            ]),

            'launchAssessmentUrl' => route('schools.assessments.create', [
                'school' => $schoolId,
                'classroom' => $classroom->id,
                'workshop' => $workshopForClass->id,
            ]),

            'assessmentsIndexUrl' => route('schools.assessments.index', [
                'school' => $schoolId,
                'classroom' => $classroom->id,
                'workshop' => $workshopForClass->id,
            ]),
        ]);
    }

    /**
     * Lista as SUBTURMAS de uma oficina da TURMA PAI.
     *
     * Rota:
     * GET /turmas/{classroom}/oficinas/{workshop}/subturmas
     * name: classrooms.workshops.subclasses.index
     */
    public function indexSubclasses(Classroom $classroom, Workshop $workshop)
    {
        // Só TURMA PAI
        abort_if($classroom->parent_classroom_id, 404);

        // Carrega escola + oficinas (com pivot) da turma PAI
        $classroom->loadMissing('school', 'workshops');

        // Oficina vinculada a ESSA turma (com pivot -> max_students)
        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        /**
         * SUBTURMAS dessa turma PAI PARA ESSA OFICINA
         * Child se vincula à oficina via pivot classroom_workshop.
         */
        $children = $classroom->children()
            ->whereHas('workshops', function ($q) use ($workshop) {
                $q->where('workshops.id', $workshop->id);
            })
            ->orderBy('name')
            ->get();

        // Contagem de alunos alocados por subturma (pra ESSA oficina)
        if ($children->isNotEmpty()) {
            $childIds = $children->pluck('id');

            $allocations = WorkshopAllocation::query()
                ->where('workshop_id', $workshop->id)
                ->whereIn('child_classroom_id', $childIds)
                ->get(['child_classroom_id', 'student_enrollment_id']);

            $allocCountByChild = $allocations
                ->groupBy('child_classroom_id')
                ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

            $children->each(function ($child) use ($allocCountByChild) {
                $child->students_count = (int) ($allocCountByChild[$child->id] ?? 0);
            });
        } else {
            $children->each(function ($child) {
                $child->students_count = 0;
            });
        }

        $stats = [
            'subclassrooms_count' => $children->count(),
            'total_allocated' => $children->sum('students_count'),
        ];

        // TOTAL de episódios elegíveis da TURMA PAI (mesma lógica da tela da PAI)
        $eligibleCount = method_exists($classroom, 'eligibleEnrollments')
            ? $classroom->eligibleEnrollments()->count()
            : 0;

        // Capacidade da oficina no pivot da turma PAI (max_students)
        $capacity = (int) ($workshopForClass->pivot->max_students ?? 0);

        return view('classrooms.workshops.subclasses-index', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,   // com pivot acessível
            'children' => $children,
            'stats' => $stats,
            'eligibleCount' => $eligibleCount,
            'capacity' => $capacity,
        ]);
    }
}
