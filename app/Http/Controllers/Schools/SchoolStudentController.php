<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class SchoolStudentController extends Controller
{
    /**
     * Lista de alunos da ESCOLA (no fundo: episódios de matrícula na escola).
     *
     * URL: /escolas/{school}/alunos
     * Rota: schools.students.index
     *
     * Por enquanto, cada linha representa um episódio de matrícula (StudentEnrollment),
     * com aluno + ano escolar. No futuro, se quiser, dá pra agrupar por aluno.
     */
    public function index(School $school, Request $request)
    {
        // TODO RBAC ESCOLA:
        // Quando tiver RBAC implementado, algo como:
        // $this->authorize('viewAnySchoolStudents', $school);

        // Filtro simples por nome do aluno (q = query string)
        $search = $request->string('q')->trim();

        $enrollmentsQuery = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->with(['student', 'gradeLevel']);

        if ($search->isNotEmpty()) {
            $term = '%'.$search.'%';

            $enrollmentsQuery->whereHas('student', function ($q) use ($term) {
                // Ajuste o campo "name" se o modelo Student tiver outro nome de coluna
                $q->where('name', 'like', $term);
            });
        }

        // Paginação básica (20 por página)
        $enrollments = $enrollmentsQuery
            ->orderBy('id', 'asc') // simples; depois você pode sofisticar (join com students pra ordenar por nome)
            ->paginate(20)
            ->withQueryString();

        return view('schools.students.index', [
            'school' => $school,
            'schoolNav' => $school,      // ativa o navbar da escola
            'enrollments' => $enrollments,
            'search' => $search,
        ]);
    }

    // Os outros métodos (create/store/show/edit/update) você pode ir criando depois.
}
