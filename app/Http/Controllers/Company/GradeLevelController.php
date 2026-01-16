<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Http\Requests\GradeLevelRequest;
use App\Models\GradeLevel;

class GradeLevelController extends Controller
{
    public function index()
    {
        $levels = GradeLevel::orderBy('sequence')->orderBy('name')->paginate(20);

        return view('grade_levels.index', compact('levels'));
    }

    public function create()
    {
        return view('grade_levels.create');
    }

    public function store(GradeLevelRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        GradeLevel::create($data);

        return redirect()
            ->route('grade-levels.index')
            ->with('success', 'Ano escolar criado com sucesso!');
    }

    public function show(GradeLevel $gradeLevel)
    {
        return view('grade_levels.show', compact('gradeLevel'));
    }

    public function edit(GradeLevel $gradeLevel)
    {
        return view('grade_levels.edit', compact('gradeLevel'));
    }

    public function update(GradeLevelRequest $request, GradeLevel $gradeLevel)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $gradeLevel->update($data);

        return redirect()
            ->route('grade-levels.index')
            ->with('success', 'Ano escolar atualizado com sucesso!');
    }

    public function destroy(GradeLevel $gradeLevel)
    {
        $gradeLevel->delete();

        return redirect()
            ->route('grade-levels.index')
            ->with('success', 'Ano escolar excluído com sucesso!');
    }
}

