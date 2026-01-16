<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Models\Classroom;
use App\Models\Workshop;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomWorkshopGradeController extends Controller
{
    public function edit(Classroom $classroom, Workshop $workshop)
    {
        // Garante que o vínculo (classroom, workshop) existe
        abort_unless($classroom->workshops()->whereKey($workshop->id)->exists(), 404);

        $grades     = GradeLevel::orderBy('sequence')->orderBy('name')->get(['id','name','sequence']);
        $selectedId = DB::table('classroom_workshop_grade')
            ->where('classroom_id', $classroom->id)
            ->where('workshop_id', $workshop->id)
            ->pluck('grade_level_id')
            ->all();

        return view('classrooms.workshops.grades', compact('classroom','workshop','grades','selectedId'));
    }

    public function update(Request $request, Classroom $classroom, Workshop $workshop)
    {
        abort_unless($classroom->workshops()->whereKey($workshop->id)->exists(), 404);

        $ids = array_map('intval', (array) $request->input('grade_level_ids', []));

        // Validação simples: todos os IDs devem existir em grade_levels
        $countValid = GradeLevel::whereIn('id', $ids)->count();
        if ($countValid !== count($ids)) {
            return back()->withErrors(['grade_level_ids' => 'Seleção de anos inválida.']);
        }

        // Sincroniza: remove o que não está, insere o que está
        DB::transaction(function () use ($classroom, $workshop, $ids) {
            DB::table('classroom_workshop_grade')
                ->where('classroom_id', $classroom->id)
                ->where('workshop_id', $workshop->id)
                ->whereNotIn('grade_level_id', $ids)
                ->delete();

            $existing = DB::table('classroom_workshop_grade')
                ->where('classroom_id', $classroom->id)
                ->where('workshop_id', $workshop->id)
                ->pluck('grade_level_id')->all();

            $toInsert = array_diff($ids, $existing);

            foreach ($toInsert as $gid) {
                DB::table('classroom_workshop_grade')->insert([
                    'classroom_id'   => $classroom->id,
                    'workshop_id'    => $workshop->id,
                    'grade_level_id' => $gid,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        });

        return redirect()->route('classrooms.show', $classroom)
                         ->with('success', 'Anos atendidos atualizados.');
    }
}
