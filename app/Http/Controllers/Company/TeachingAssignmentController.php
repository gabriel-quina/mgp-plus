<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreTeachingAssignmentRequest, UpdateTeachingAssignmentRequest};
use App\Models\{RoleAssignment, School, Teacher, TeachingAssignment, User};
use Illuminate\Database\QueryException;

class TeachingAssignmentController extends Controller
{
    public function create(Teacher $teacher)
    {
        // Cidades liberadas por acesso (our_*) + cidades de vínculos municipais ativos
        $accessCityIds = $teacher->cityAccesses()->pluck('city_id')->all();
        $municipalCityIds = $teacher->engagements()
            ->where('engagement_type', 'municipal')
            ->where('status', 'active')
            ->pluck('city_id')
            ->filter() // tira null
            ->all();

        $allowedCityIds = array_values(array_unique(array_merge($accessCityIds, $municipalCityIds)));

        // Sugere só escolas em cidades onde ele pode atuar
        $schools = School::with('city')
            ->when(count($allowedCityIds) > 0, fn ($q) => $q->whereIn('city_id', $allowedCityIds))
            ->orderBy('name')
            ->get();

        // Vínculos do professor (para escolher "qual financia", opcional)
        $engagements = $teacher->engagements()
            ->orderBy('engagement_type')
            ->orderByDesc('start_date')
            ->get();

        $assignment = new TeachingAssignment;

        return view('company.teaching_assignments.create', compact('teacher', 'assignment', 'schools', 'engagements'));
    }

    public function store(Teacher $teacher, StoreTeachingAssignmentRequest $request)
    {
        $data = $request->validated();

        // não precisa setar teacher_id manualmente, mas não faz mal
        $data['teacher_id'] = $teacher->id;

        $assignment = $teacher->assignments()->create($data);

        // ✅ RBAC: alocação em escola gera acesso à escola
        if ($teacher->cpf) {
            $user = User::where('cpf', $teacher->cpf)->first();

            if ($user) {
                $assignment->load('school');

                if ($assignment->school) {
                    $user->assignRole('school_teacher', $assignment->school);
                }
            }
        }

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Alocação criada com sucesso e acesso à escola concedido.');
    }

    public function edit(Teacher $teacher, TeachingAssignment $teaching_assignment)
    {
        abort_unless($teaching_assignment->teacher_id === $teacher->id, 404);

        $accessCityIds = $teacher->cityAccesses()->pluck('city_id')->all();
        $municipalCityIds = $teacher->engagements()
            ->where('engagement_type', 'municipal')
            ->where('status', 'active')
            ->pluck('city_id')
            ->filter()
            ->all();

        $allowedCityIds = array_values(array_unique(array_merge($accessCityIds, $municipalCityIds)));

        $schools = School::with('city')
            ->when(count($allowedCityIds) > 0, fn ($q) => $q->whereIn('city_id', $allowedCityIds))
            ->orderBy('name')
            ->get();

        $engagements = $teacher->engagements()
            ->orderBy('engagement_type')
            ->orderByDesc('start_date')
            ->get();

        return view('company.teaching_assignments.edit', [
            'teacher' => $teacher,
            'assignment' => $teaching_assignment,
            'schools' => $schools,
            'engagements' => $engagements,
        ]);
    }

    public function update(Teacher $teacher, UpdateTeachingAssignmentRequest $request, TeachingAssignment $teaching_assignment)
    {
        abort_unless($teaching_assignment->teacher_id === $teacher->id, 404);

        $oldSchoolId = $teaching_assignment->school_id;

        $teaching_assignment->update($request->validated());

        // ✅ Se mudou a escola, garante acesso à nova também
        if ($teacher->cpf && $oldSchoolId != $teaching_assignment->school_id) {
            $user = User::where('cpf', $teacher->cpf)->first();

            if ($user) {
                $teaching_assignment->load('school');

                if ($teaching_assignment->school) {
                    $user->assignRole('school_teacher', $teaching_assignment->school);
                }
            }
        }

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Alocação atualizada com sucesso.');
    }

    public function destroy(Teacher $teacher, TeachingAssignment $teaching_assignment)
    {
        abort_unless($teaching_assignment->teacher_id === $teacher->id, 404);

        $schoolId = $teaching_assignment->school_id;

        try {
            $teaching_assignment->delete();
        } catch (QueryException $e) {
            report($e);

            return redirect()
                ->route('admin.teachers.show', $teacher)
                ->withErrors('Não foi possível excluir esta alocação. Verifique dependências (ex.: aulas).');
        }

        // RBAC pós-delete: se falhar, não deve impedir o sucesso do delete
        try {
            if ($schoolId && $teacher->cpf) {
                $user = User::where('cpf', $teacher->cpf)->first();

                if ($user) {
                    $stillAssigned = TeachingAssignment::where('teacher_id', $teacher->id)
                        ->where('school_id', $schoolId)
                        ->exists();

                    if (! $stillAssigned) {
                        if (method_exists($user, 'removeRole')) {
                            $school = School::find($schoolId);
                            if ($school) {
                                $user->removeRole('school_teacher', $school);
                            }
                        } else {
                            RoleAssignment::query()
                                ->where('user_id', $user->id)
                                ->where('scope_type', School::class)
                                ->where('scope_id', $schoolId)
                                ->whereHas('role', function ($q) {
                                    $q->where('name', 'school_teacher'); // <= troque slug por name
                                })
                                ->delete();
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Alocação excluída com sucesso e acesso à escola atualizado.');
    }
}
