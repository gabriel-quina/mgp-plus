<?php

namespace App\Http\Controllers\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\StudentEnrollment;
use App\Models\WorkshopAllocation;

class ChildController extends Controller
{
    /**
     * Exibe a SUBTURMA (child):
     * - Lista SOMENTE os alunos alocados para a oficina vinculada à subturma.
     * - Se não houver oficina vinculada, mostra grupo vazio com aviso.
     *
     * Rota: GET /turmas/{parent}/subturmas/{classroom} -> name: subclassrooms.show
     */
    public function show(Classroom $parent, Classroom $classroom)
    {
        // Garantia: só CHILD aqui e pertencente a este PAI
        abort_if($classroom->parent_classroom_id === null, 404);
        abort_if($classroom->parent_classroom_id !== $parent->id, 404);

        // Carrega dados básicos
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        // Cada subturma deve estar vinculada a UMA oficina
        $workshop = $classroom->workshops()->first();

        if (! $workshop) {
            // Sem oficina atrelada: nenhuma alocação deve aparecer
            return view('classrooms.workshops.group', [
                'pageTitle' => 'Subturma — '.$classroom->name,
                'headerTitle' => $classroom->name,
                'contextLine' => sprintf(
                    'Subturma de: <a href="%s">%s</a> · Escola: <strong>%s</strong> · Ano: <strong>%d</strong> · Turno: <strong>%s</strong>',
                    e(route('classrooms.show', $parent)),
                    e($parent->name),
                    e(optional($classroom->school)->name ?? '—'),
                    $classroom->academic_year,
                    $classroom->shift ?? '—',
                ),
                'workshopLine' => 'Oficina: —',

                'studentsLabel' => 'Alunos alocados nesta subturma',
                'studentsCount' => 0,

                // capacidade null → o card de capacidade nem aparece
                'capacity' => null,
                'tableTitle' => 'Alunos alocados',
                'emptyMessage' => 'Nenhum aluno alocado.',

                'classroom' => $classroom,
                'workshop' => null,
                'enrollments' => collect(),

                // volta pra turma PAI
                'backUrl' => route('classrooms.show', $parent),

                // sem oficina → não faz sentido lançar aula/avaliação
                'launchLessonUrl' => null,
                'lessonsIndexUrl' => null,
                'launchAssessmentUrl' => null,
                'assessmentsIndexUrl' => null,
            ])->with('warning', 'Esta subturma ainda não possui oficina vinculada.');
        }

        // IDs de episódios alocados nesta subturma para esta oficina
        $allocatedIds = WorkshopAllocation::query()
            ->where('child_classroom_id', $classroom->id)
            ->where('workshop_id', $workshop->id)
            ->pluck('student_enrollment_id');

        // Carrega apenas os episódios alocados (ordenados por nome do aluno)
        $enrollments = StudentEnrollment::query()
            ->with(['student', 'gradeLevel', 'school'])
            ->join('students', 'students.id', '=', 'student_enrollments.student_id')
            ->whereIn('student_enrollments.id', $allocatedIds)
            ->orderBy('students.name')
            ->select('student_enrollments.*') // evita ambiguidade após join
            ->paginate(20);

        $max = optional($workshop->pivot)->max_students;

        return view('classrooms.workshops.group', [
            'pageTitle' => 'Subturma — '.$classroom->name,
            'headerTitle' => $classroom->name,
            'contextLine' => sprintf(
                'Subturma de: <a href="%s">%s</a> · Escola: <strong>%s</strong> · Ano: <strong>%d</strong> · Turno: <strong>%s</strong>',
                e(route('classrooms.show', $parent)),
                e($parent->name),
                e(optional($classroom->school)->name ?? '—'),
                $classroom->academic_year,
                $classroom->shift ?? '—',
            ),
            'workshopLine' => sprintf(
                'Oficina: <strong>%s</strong>%s',
                e($workshop->name),
                $max ? ' · Capacidade por subturma: <strong>'.e($max).'</strong>' : ''
            ),

            'studentsLabel' => 'Alunos alocados nesta subturma',
            'studentsCount' => $enrollments->total(),

            'capacity' => $max ?? 0,
            'capacityLabel' => 'Capacidade por subturma',

            'tableTitle' => 'Alunos alocados',
            'emptyMessage' => 'Nenhum aluno alocado.',

            'classroom' => $classroom,
            'workshop' => $workshop,
            'enrollments' => $enrollments,

            // Voltar para a lista de subturmas daquela oficina
            'backUrl' => route('classrooms.workshops.subclasses.index', [$parent, $workshop]),

            // Aulas (para SUBTURMA + oficina)
            'launchLessonUrl' => route('classrooms.lessons.create', [
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ]),
            'lessonsIndexUrl' => route('classrooms.lessons.index', [
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ]),

            // ✅ Avaliações (para SUBTURMA + oficina)
            'launchAssessmentUrl' => route('classrooms.assessments.create', [
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ]),
            'assessmentsIndexUrl' => route('classrooms.assessments.index', [
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ]),
        ]);
    }
}
