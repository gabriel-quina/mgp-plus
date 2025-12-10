<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherCityAccess;
use App\Models\TeacherEngagement;
use App\Models\TeachingAssignment;
use App\Services\TeacherUserService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : null;

        $teachers = Teacher::query()
            ->when($q !== '', fn ($query) => $query->search($q))
            ->when(! is_null($isActive), fn ($query) => $query->where('is_active', $isActive))
            ->alphabetical()                // escopo herdado de Person; ordena por nome
            ->paginate(20)                  // padrão do projeto
            ->withQueryString();            // preserva filtros na paginação

        return view('teachers.index', compact('teachers', 'q', 'isActive'));
    }

    public function create()
    {
        $teacher = new Teacher;

        return view('teachers.create', compact('teacher'));
    }

    public function store(Request $request, TeacherUserService $teacherUserService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'social_name' => ['nullable', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $cpf = preg_replace('/\D+/', '', (string) $data['cpf']);

        // valida 11 dígitos
        if (strlen($cpf) !== 11) {
            return back()->withErrors(['cpf' => 'CPF deve conter 11 dígitos.'])->withInput();
        }

        // bloqueia sequências inválidas
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        // valida dígitos verificadores
        $digits = array_map('intval', str_split($cpf));

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $digits[$i] * (10 - $i);
        }
        $rem = $sum % 11;
        $dv1 = ($rem < 2) ? 0 : 11 - $rem;

        if ($digits[9] !== $dv1) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $digits[$i] * (11 - $i);
        }
        $rem = $sum % 11;
        $dv2 = ($rem < 2) ? 0 : 11 - $rem;

        if ($digits[10] !== $dv2) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        // único na tabela teachers
        if (Teacher::where('cpf', $cpf)->exists()) {
            return back()->withErrors(['cpf' => 'Este CPF já está em uso.'])->withInput();
        }

        $data['cpf'] = $cpf;

        $teacher = Teacher::create($data);

        $teacherUserService->syncFromTeacher($teacher);

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Professor criado e usuário gerado com senha inicial.');
    }

    public function show(Teacher $teacher)
    {
        // Vínculos — ordena por tipo, status, e nome da cidade (quando municipal)
        $engagements = TeacherEngagement::query()
            ->where('teacher_id', $teacher->id)
            ->with(['city']) // evita N+1
            ->orderBy('engagement_type')         // our_clt/our_pj/our_temporary/municipal
            ->orderBy('status')                  // active/suspended/ended
            ->leftJoin('cities', 'cities.id', '=', 'teacher_engagements.city_id')
            ->select('teacher_engagements.*')
            ->orderBy('cities.name')             // cidade quando existir
            ->paginate(20)
            ->withQueryString();

        // Acessos de cidade — ordena por nome da cidade (JOIN para SQLite)
        $cityAccesses = TeacherCityAccess::query()
            ->where('teacher_id', $teacher->id)
            ->with(['city.state'])
            ->leftJoin('cities', 'cities.id', '=', 'teacher_city_access.city_id')
            ->select('teacher_city_access.*')
            ->orderBy('cities.name')
            ->paginate(20)
            ->withQueryString();

        // Alocações — ordena por cidade da escola, depois escola, depois ano/turno (JOINs)
        $assignments = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->with(['school.city', 'engagement.city'])
            ->leftJoin('schools', 'schools.id', '=', 'teaching_assignments.school_id')
            ->leftJoin('cities', 'cities.id', '=', 'schools.city_id')
            ->select('teaching_assignments.*') // mantém o model base
            ->orderBy('cities.name')
            ->orderBy('schools.name')
            ->orderByDesc('academic_year')
            ->orderBy('shift')
            ->paginate(20)
            ->withQueryString();

        // (opcional) contadores pra header/aba
        $teacher->loadCount(['engagements', 'cityAccesses', 'assignments']);

        return view('teachers.show', compact('teacher', 'engagements', 'cityAccesses', 'assignments'));
    }

    public function edit(Teacher $teacher)
    {
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher, TeacherUserService $teacherUserService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'social_name' => ['nullable', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'birthdate' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $cpf = preg_replace('/\D+/', '', (string) $data['cpf']);

        // Se já tinha CPF, não deixa mudar
        if ($teacher->cpf && $cpf !== $teacher->cpf) {
            return back()->withErrors(['cpf' => 'CPF não pode ser alterado após a criação.'])->withInput();
        }

        // Se era legacy sem CPF, pode preencher uma vez (com validação básica)
        if (! $teacher->cpf) {
            if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
                return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
            }

            if (Teacher::where('cpf', $cpf)->where('id', '!=', $teacher->id)->exists()) {
                return back()->withErrors(['cpf' => 'Este CPF já está em uso.'])->withInput();
            }

            $data['cpf'] = $cpf;
        } else {
            unset($data['cpf']);
        }

        $teacher->update($data);

        $teacherUserService->syncFromTeacher($teacher);

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Professor atualizado e usuário sincronizado.');
    }

    public function destroy(Teacher $teacher)
    {
        try {
            $teacher->delete();

            return redirect()
                ->route('teachers.index')
                ->with('success', 'Professor excluído com sucesso.');
        } catch (QueryException $e) {
            report($e);

            return redirect()
                ->route('teachers.show', $teacher)
                ->withErrors('Não foi possível excluir este professor. Verifique vínculos, acessos ou alocações associadas.');
        }
    }
}
