<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SchoolTeacherController extends Controller
{
    public function index(School $school, Request $request)
    {
        $q = trim((string) $request->get('q'));

        // is_active pode vir como '', '1', '0'
        $activeRaw = $request->get('is_active');
        $isActive = ($activeRaw === null || $activeRaw === '')
            ? null
            : (bool) ((int) $activeRaw);

        $teachersQuery = Teacher::query()
            ->whereHas('assignments', function ($a) use ($school) {
                $a->where('school_id', $school->id);
            })
            ->withCount([
                'assignments as assignments_in_school_count' => function ($a) use ($school) {
                    $a->where('school_id', $school->id);
                },
            ]);

        if (! is_null($isActive)) {
            $teachersQuery->where('is_active', $isActive);
        }

        if ($q !== '') {
            $teachersQuery->where(function ($t) use ($q) {
                $t->where('name', 'like', "%{$q}%")
                    ->orWhere('social_name', 'like', "%{$q}%")
                    ->orWhere('cpf', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $teachers = $teachersQuery
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('schools.teachers.index', [
            'school' => $school,
            'schoolNav' => $school,
            'teachers' => $teachers,
            'q' => $q,
            'isActive' => $isActive,
        ]);
    }

    public function show(School $school, Teacher $teacher)
    {
        // garante vínculo real com a escola
        $linked = $teacher->assignments()
            ->where('school_id', $school->id)
            ->exists();

        abort_unless($linked, 404);

        // contadores usados na sua view teachers.show
        $teacher->loadCount([
            'engagements',
            'cityAccesses',
            'assignments',
        ]);

        // mantém o mesmo padrão do show master,
        // mas filtra as alocações só para a escola atual
        $engagements = $teacher->engagements()
            ->with(['city'])
            ->latest()
            ->paginate(10);

        $cityAccesses = $teacher->cityAccesses()
            ->with(['city.state'])
            ->latest()
            ->paginate(10);

        $assignments = $teacher->assignments()
            ->where('school_id', $school->id)
            ->with(['school.city.state', 'engagement.city'])
            ->latest()
            ->paginate(10);

        // Reaproveita a view master por enquanto
        return view('schools.teachers.show', [
            'school' => $school,
            'schoolNav' => $school,
            'teacher' => $teacher,
            'engagements' => $engagements,
            'cityAccesses' => $cityAccesses,
            'assignments' => $assignments,
        ]);
    }
}
