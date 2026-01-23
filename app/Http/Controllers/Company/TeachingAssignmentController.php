<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreTeachingAssignmentRequest, UpdateTeachingAssignmentRequest};
use App\Models\{
    School,
    Teacher,
    TeachingAssignment,
    User,
    UserScope,
    SchoolRole,
    SchoolRoleAssignment
};
use Illuminate\Database\QueryException;

class TeachingAssignmentController extends Controller
{
    private function normalizedCpf(?string $cpf): ?string
    {
        $cpf = preg_replace('/\D+/', '', (string) $cpf) ?: null;
        return $cpf ?: null;
    }

    private function userForTeacher(Teacher $teacher): ?User
    {
        $cpf = $this->normalizedCpf($teacher->cpf);
        if (! $cpf) return null;

        return User::query()->where('cpf', $cpf)->first();
    }

    private function teacherRoleId(): int
    {
        // Seu RbacSeeder cria school_roles com name = 'teacher'
        $roleId = SchoolRole::query()->where('name', 'teacher')->value('id');

        if (! $roleId) {
            abort(500, "RBAC inconsistente: role 'teacher' não encontrada em school_roles.");
        }

        return (int) $roleId;
    }

    private function ensureTeacherHasSchoolAccess(User $user, int $schoolId): void
    {
        // Garante que o usuário esteja no escopo school
        UserScope::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['scope' => 'school']
        );

        // Garante a role 'teacher' para aquela escola (idempotente)
        SchoolRoleAssignment::query()->firstOrCreate([
            'user_id' => $user->id,
            'school_role_id' => $this->teacherRoleId(),
            'school_id' => $schoolId,
        ]);
    }

    private function revokeTeacherSchoolAccessIfNoLongerAssigned(User $user, Teacher $teacher, int $schoolId): void
    {
        $stillAssigned = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('school_id', $schoolId)
            ->exists();

        if ($stillAssigned) {
            return;
        }

        SchoolRoleAssignment::query()
            ->where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->where('school_role_id', $this->teacherRoleId())
            ->delete();
    }

    public function create(Teacher $teacher)
    {
        // Cidades liberadas por acesso (our_*) + cidades de vínculos municipais ativos
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

        $assignment = new TeachingAssignment;

        return view('company.teaching_assignments.create', compact('teacher', 'assignment', 'schools', 'engagements'));
    }

    public function store(Teacher $teacher, StoreTeachingAssignmentRequest $request)
    {
        $data = $request->validated();
        $data['teacher_id'] = $teacher->id;

        $assignment = $teacher->assignments()->create($data);

        // ✅ RBAC (seu RBAC): alocação em escola => SchoolRoleAssignment 'teacher' para o User do professor
        $user = $this->userForTeacher($teacher);
        if ($user && $assignment->school_id) {
            $this->ensureTeacherHasSchoolAccess($user, (int) $assignment->school_id);
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

        $oldSchoolId = (int) $teaching_assignment->school_id;

        $teaching_assignment->update($request->validated());

        $newSchoolId = (int) $teaching_assignment->school_id;

        $user = $this->userForTeacher($teacher);

        if ($user && $newSchoolId) {
            // Garante acesso à escola atual (se mudou ou não)
            $this->ensureTeacherHasSchoolAccess($user, $newSchoolId);

            // Se mudou a escola, revoga a antiga caso não exista mais alocação nela
            if ($oldSchoolId && $oldSchoolId !== $newSchoolId) {
                $this->revokeTeacherSchoolAccessIfNoLongerAssigned($user, $teacher, $oldSchoolId);
            }
        }

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Alocação atualizada com sucesso.');
    }

    public function destroy(Teacher $teacher, TeachingAssignment $teaching_assignment)
    {
        abort_unless($teaching_assignment->teacher_id === $teacher->id, 404);

        $schoolId = (int) $teaching_assignment->school_id;

        try {
            $teaching_assignment->delete();
        } catch (QueryException $e) {
            report($e);

            return redirect()
                ->route('admin.teachers.show', $teacher)
                ->withErrors('Não foi possível excluir esta alocação. Verifique dependências (ex.: aulas).');
        }

        // ✅ RBAC pós-delete (best effort)
        try {
            $user = $this->userForTeacher($teacher);
            if ($user && $schoolId) {
                $this->revokeTeacherSchoolAccessIfNoLongerAssigned($user, $teacher, $schoolId);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Alocação excluída com sucesso e acesso à escola atualizado.');
    }
}

