<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolClassroomController extends Controller
{
    public function index(School $school, Request $request)
    {
        $q = $request->get('q', '');
        $yr = $request->get('year');
        $sh = $request->get('shift');

        $classroomsQuery = Classroom::query()
            ->with(['gradeLevels']) // segue o padrão do seu index MASTER
            ->where('school_id', $school->id);

        if ($q !== '') {
            $classroomsQuery->where('name', 'like', "%{$q}%");
        }

        if ($yr) {
            $classroomsQuery->where('academic_year', (int) $yr);
        }

        if ($sh) {
            $classroomsQuery->where('shift', $sh);
        }

        // Ordenação que ajuda a leitura quando mistura base + grupos
        $classrooms = $classroomsQuery
            ->orderByRaw('parent_classroom_id is null desc')
            ->orderBy('academic_year', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Mesmo “enriquecimento” do MASTER, sem assumir que child tem esse método
        $classrooms->getCollection()->transform(function ($classroom) {
            if (method_exists($classroom, 'eligibleEnrollments') && ! $classroom->parent_classroom_id) {
                $classroom->total_all_students = $classroom->eligibleEnrollments()->count();
            }

            return $classroom;
        });

        return view('schools.classrooms.index', [
            'school' => $school,
            'schoolNav' => $school,
            'classrooms' => $classrooms,
            'q' => $q,
            'yr' => $yr,
            'sh' => $sh,
        ]);
    }

    public function show(School $school, Classroom $classroom)
    {
        // Segurança básica sem mexer nas rotas
        abort_if((int) $classroom->school_id !== (int) $school->id, 404);

        // Reaproveita suas telas MASTER já prontas
        if ($classroom->parent_classroom_id) {
            return redirect()->route('subclassrooms.show', [
                'parent' => $classroom->parent_classroom_id,
                'classroom' => $classroom->id,
            ]);
        }

        return redirect()->route('classrooms.show', $classroom);
    }
}
