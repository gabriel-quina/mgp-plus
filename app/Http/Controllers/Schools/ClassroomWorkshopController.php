<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassroomWorkshopController extends Controller
{
    public function create(Classroom $classroom)
    {
        // Se seu projeto vincula oficinas à escola, filtre aqui pelas oficinas da mesma escola.
        $workshops = Workshop::orderBy('name')->pluck('name','id');

        return view('classrooms.workshops.create', compact('classroom','workshops'));
    }

    public function store(Request $request, Classroom $classroom)
    {
        $data = $request->validate(
            [
                'workshop_id'  => ['required', 'integer',
                    Rule::unique('classroom_workshop')->where(fn($q) => $q
                        ->where('classroom_id', $classroom->id)
                        ->where('workshop_id', $request->workshop_id)
                    ),
                    'exists:workshops,id',
                ],
                'max_students' => ['required', 'integer', 'min:1', 'max:9999'],
            ],
            [
                'workshop_id.unique' => 'Esta oficina já está vinculada a esta turma.',
            ],
            [
                'workshop_id'  => 'Oficina',
                'max_students' => 'Capacidade',
            ]
        );

        $classroom->workshops()->attach($data['workshop_id'], [
            'max_students' => $data['max_students'],
        ]);

        return redirect()->route('classrooms.show', $classroom)
                         ->with('success', 'Oficina vinculada à turma.');
    }

    public function edit(Classroom $classroom, Workshop $workshop)
    {
        // garantir que o vínculo exista
        abort_unless($classroom->workshops()->whereKey($workshop->id)->exists(), 404);

        $current = $classroom->workshops()->whereKey($workshop->id)->first();

        return view('classrooms.workshops.edit', [
            'classroom' => $classroom,
            'workshop'  => $workshop,
            'pivot'     => $current->pivot,
        ]);
    }

    public function update(Request $request, Classroom $classroom, Workshop $workshop)
    {
        abort_unless($classroom->workshops()->whereKey($workshop->id)->exists(), 404);

        $data = $request->validate([
            'max_students' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $classroom->workshops()->updateExistingPivot($workshop->id, [
            'max_students' => $data['max_students'],
        ]);

        return redirect()->route('classrooms.show', $classroom)
                         ->with('success', 'Capacidade atualizada.');
    }

    public function destroy(Classroom $classroom, Workshop $workshop)
    {
        $classroom->workshops()->detach($workshop->id);

        return redirect()->route('classrooms.show', $classroom)
                         ->with('success', 'Vínculo removido.');
    }
}

