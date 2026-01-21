<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreTeacherEngagementRequest;
use App\Http\Requests\UpdateTeacherEngagementRequest;
use App\Models\City;
use App\Models\Teacher;
use App\Models\TeacherEngagement;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TeacherEngagementController extends Controller
{
    public function index(Teacher $teacher, Request $request)
    {
        $type   = $request->query('type');
        $status = $request->query('status');

        $engagements = $teacher->engagements()
            ->when($type, fn($q) => $q->where('engagement_type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->with('city')
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('company.teacher_engagements.index', compact('teacher', 'engagements', 'type', 'status'));
    }

    public function create(Teacher $teacher)
    {
        $engagement = new TeacherEngagement();
        $cities = City::orderBy('name')->pluck('name', 'id');
        return view('company.teacher_engagements.create', compact('teacher', 'engagement', 'cities'));
    }

    public function store(Teacher $teacher, StoreTeacherEngagementRequest $request)
    {
        $data = $request->validated();
        $data['teacher_id'] = $teacher->id;

        // Se não for municipal, garanta city_id nulo
        if (in_array($data['engagement_type'], ['our_clt','our_pj','our_temporary'], true)) {
            $data['city_id'] = null;
        }

        $teacher->engagements()->create($data);

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Vínculo criado com sucesso.');
    }

    public function edit(Teacher $teacher, TeacherEngagement $teacher_engagement)
    {
        // Garanta o aninhamento correto
        abort_unless($teacher_engagement->teacher_id === $teacher->id, 404);

        $cities = City::orderBy('name')->pluck('name', 'id');
        return view('company.teacher_engagements.edit', [
            'teacher'    => $teacher,
            'engagement' => $teacher_engagement,
            'cities'     => $cities,
        ]);
    }

    public function update(Teacher $teacher, UpdateTeacherEngagementRequest $request, TeacherEngagement $teacher_engagement)
    {
        abort_unless($teacher_engagement->teacher_id === $teacher->id, 404);

        $data = $request->validated();
        if (in_array($data['engagement_type'], ['our_clt','our_pj','our_temporary'], true)) {
            $data['city_id'] = null;
        }

        $teacher_engagement->update($data);

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    public function destroy(Teacher $teacher, TeacherEngagement $teacher_engagement)
    {
        abort_unless($teacher_engagement->teacher_id === $teacher->id, 404);

        try {
            $teacher_engagement->delete();

            return redirect()
                ->route('admin.teachers.show', $teacher)
                ->with('success', 'Vínculo excluído com sucesso.');
        } catch (QueryException $e) {
            report($e);

            return redirect()
                ->route('admin.teachers.show', $teacher)
                ->withErrors('Não foi possível excluir este vínculo. Verifique se há dependências.');
        }
    }
}

