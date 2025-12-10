<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class SchoolEnrollmentController extends Controller
{
    public function index(School $school, Request $request)
    {
        $q = trim((string) $request->get('q'));
        $yr = $request->get('year');
        $sh = $request->get('shift');
        $st = $request->get('status');

        $query = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->with(['student', 'gradeLevel', 'school']);

        // Busca por aluno (nome, CPF, e-mail) - ajuste os campos conforme seu model de Student
        if ($q !== '') {
            $query->whereHas('student', function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                    ->orWhere('cpf', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // Ano letivo (no seu index admin você usa academic_year)
        if (! empty($yr)) {
            $query->where('academic_year', (int) $yr);
        }

        if (! empty($sh)) {
            $query->where('shift', $sh);
        }

        if (! empty($st)) {
            $query->where('status', $st);
        }

        $enrollments = $query
            ->orderByDesc('academic_year')
            ->orderBy('shift')
            ->paginate(20)
            ->withQueryString();

        return view('schools.enrollments.index', [
            'school' => $school,
            'schoolNav' => $school, // ativa navbar da escola no layout
            'enrollments' => $enrollments,
            'q' => $q,
            'yr' => $yr,
            'sh' => $sh,
            'st' => $st,
        ]);
    }

    // Deixe os outros métodos para depois, se quiser.
    // Como suas rotas incluem create/store/show/edit/update,
    // você pode implementar mais tarde sem quebrar o index.
}
