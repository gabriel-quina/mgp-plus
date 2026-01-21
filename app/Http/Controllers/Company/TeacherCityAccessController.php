<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreTeacherCityAccessRequest;
use App\Models\City;
use App\Models\Teacher;
use App\Models\TeacherCityAccess;
use Illuminate\Http\Request;

class TeacherCityAccessController extends Controller
{
    // Exibimos e gerimos pela página do professor; aqui só create/store/destroy

    public function create(Teacher $teacher)
    {
        // Sugerir apenas cidades que ainda não têm acesso
        $used = $teacher->cityAccesses()->pluck('city_id')->all();

        $cities = City::orderBy('name')
            ->when(count($used) > 0, fn($q) => $q->whereNotIn('id', $used))
            ->pluck('name', 'id');

        return view('company.teacher_city_access.create', compact('teacher', 'cities'));
    }

    public function store(Teacher $teacher, StoreTeacherCityAccessRequest $request)
    {
        $teacher->cityAccesses()->create($request->validated());

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Cidade adicionada ao acesso do professor.');
    }

    public function destroy(Teacher $teacher, TeacherCityAccess $teacher_city_access)
    {
        // garante aninhamento correto
        abort_unless($teacher_city_access->teacher_id === $teacher->id, 404);

        $teacher_city_access->delete();

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Acesso à cidade removido com sucesso.');
    }
}

