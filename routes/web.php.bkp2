<?php

use App\Http\Controllers\CityController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\Classrooms\AssessmentController;
use App\Http\Controllers\Classrooms\ChildController as ClassroomChildController;
use App\Http\Controllers\Classrooms\LessonController;
use App\Http\Controllers\Classrooms\ParentController as ClassroomParentController;
use App\Http\Controllers\Classrooms\WorkshopClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeLevelController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\SchoolGradeLevelStudentsController;
use App\Http\Controllers\Reports\SchoolGroupsOverviewReportController;
use App\Http\Controllers\Reports\SchoolReportsController;
use App\Http\Controllers\Reports\SchoolUnallocatedStudentsReportController;
use App\Http\Controllers\Reports\SchoolWorkshopCapacityReportController;
use App\Http\Controllers\Reports\SchoolWorkshopsOverviewReportController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\Schools\SchoolClassroomController;
use App\Http\Controllers\Schools\SchoolEnrollmentController;
use App\Http\Controllers\Schools\SchoolStudentController;
use App\Http\Controllers\Schools\SchoolTeacherController;
// NOVOS CONTROLLERS (escopo escola / relatórios por escola)
// -> você vai criá-los aos poucos. As rotas só vão "quebrar" se forem acessadas antes disso.
use App\Http\Controllers\SchoolWorkshopController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\TeacherCityAccessController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherEngagementController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\WorkshopDistributionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Convenções usadas:
| - Paths em PT-BR (ex.: /turmas, /escolas)
| - Nomes de rota em inglês (ex.: classrooms.index)
| - Agrupadas por domínio (Home, Cadastros, Turmas, etc.)
|
| RBAC (ainda não implementado):
| - Escopo MASTER (empresa): usuários que enxergam a rede inteira.
|   -> Futuro middleware: can:access-master
|
| - Escopo ESCOLA: usuários ligados a uma escola específica.
|   -> Futuro middleware: can:access-school,school
|
| - Futuro escopo CIDADE (opcional): coordenador por cidade.
|
*/

/*
|--------------------------------------------------------------------------
| 0. Autenticação (Laravel Breeze/Jetstream)
|--------------------------------------------------------------------------
|
| As rotas padrão de login/registro estão em auth.php.
| Aqui é só um require do arquivo gerado pelo Breeze/Jetstream.
|
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| 1. Home / Dashboard
|--------------------------------------------------------------------------
*/

/**
 * Página inicial (home pública da aplicação).
 * Pode ser uma landing pública ou um redirect pro login.
 */
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| 2. APIs auxiliares
|--------------------------------------------------------------------------
|
| Rotas de apoio para selects, autocompletes, etc.
| Avalie se no futuro elas também devem ser protegidas por auth.
|
*/

/**
 * Busca de escolas (usada em selects/autocomplete).
 *
 * GET /api/escolas/buscar
 */
Route::get('/api/escolas/buscar', [SchoolController::class, 'search'])
    ->name('schools.search');

/*
|--------------------------------------------------------------------------
| 3. Alunos (escopo MASTER)
|--------------------------------------------------------------------------
|
| Gestão global de alunos.
| Observação: matrícula inicial é criada junto com o aluno (StudentEnrollment).
|
| TODO RBAC MASTER:
| - Quando implementar RBAC, proteger esse bloco com middleware:
|   -> Route::middleware(['auth', 'can:access-master'])->group(function () { ... });
|
*/

Route::resource('alunos', StudentController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
    ->parameters(['alunos' => 'student'])
    ->names('students');

/*
|--------------------------------------------------------------------------
| 4. Professores + Vínculos (escopo MASTER)
|--------------------------------------------------------------------------
*/

/**
 * Professores (cadastro global).
 * Path: /professores
 */
Route::resource('professores', TeacherController::class)
    ->names('teachers')
    ->parameters(['professores' => 'teacher']);

/**
 * Vínculos de professor (engagements)
 * Path: /professores/{teacher}/vinculos
 */
Route::resource('professores.vinculos', TeacherEngagementController::class)
    ->except(['show']) // a página "show" é a do próprio professor
    ->names('teacher-engagements')
    ->parameters([
        'professores' => 'teacher',
        'vinculos' => 'teacher_engagement',
    ]);

/**
 * Cidades em que o professor pode atuar (city access)
 * Path: /professores/{teacher}/cidades
 */
Route::resource('professores.cidades', TeacherCityAccessController::class)
    ->only(['create', 'store', 'destroy'])
    ->names('teacher-city-access')
    ->parameters([
        'professores' => 'teacher',
        'cidades' => 'teacher_city_access',
    ]);

/**
 * Alocações de professor em turmas/oficinas (teaching assignments)
 * Path: /professores/{teacher}/alocacoes
 */
Route::resource('professores.alocacoes', TeachingAssignmentController::class)
    ->except(['index', 'show']) // gerido na página do professor
    ->names('teaching-assignments')
    ->parameters([
        'professores' => 'teacher',
        'alocacoes' => 'teaching_assignment',
    ]);

/*
|--------------------------------------------------------------------------
| 5. Cadastros básicos: Cidades, Escolas, Anos Escolares (MASTER)
|--------------------------------------------------------------------------
|
| Cadastros estruturais da rede. Apenas admin MASTER deve alterar.
|
| TODO RBAC MASTER:
| - Proteger com middleware(['auth', 'can:access-master']) no futuro.
|
*/

/**
 * Cidades
 * Path: /cidades
 */
Route::resource('cidades', CityController::class)
    ->names('cities')
    ->parameters(['cidades' => 'city']);

/**
 * Escolas
 * Path: /escolas
 *
 * Importante:
 * - schools.index/show/etc. são a visão MASTER das escolas.
 * - Dentro do escopo escola teremos rotas aninhadas: /escolas/{school}/...
 */
Route::resource('escolas', SchoolController::class)
    ->names('schools')
    ->parameters(['escolas' => 'school']);

/**
 * Anos Escolares (GradeLevel)
 * Path: /anos-escolares
 */
Route::resource('anos-escolares', GradeLevelController::class)
    ->names('grade-levels')
    ->parameters(['anos-escolares' => 'grade-level']);

/*
|--------------------------------------------------------------------------
| 6. Matrículas (StudentEnrollment) - escopo MASTER
|--------------------------------------------------------------------------
|
| Visão global de episódios de matrícula.
| Hoje index é usado como listagem somente leitura.
|
| Criação/edição da matrícula inicial continua ancorada no fluxo de Aluno.
|
*/

Route::resource('matriculas', StudentEnrollmentController::class)
    ->names('enrollments')
    ->parameters(['matriculas' => 'enrollment']);

/*
|--------------------------------------------------------------------------
| 7. Turmas (Classrooms) + Subturmas + Oficinas (escopo MASTER)
|--------------------------------------------------------------------------
|
| Essas rotas representam o modelo "turma PAI + subturma" no nível da rede.
| No novo desenho, as subturmas se aproximam do conceito de "grupo de oficina".
|
*/

/**
 * Turmas (resource base) — sem show.
 * Path: /turmas
 * Controller: ClassroomController
 *
 * Usado mais como visão administrativa (lista de turmas).
 */
Route::resource('turmas', ClassroomController::class)
    ->except(['show'])
    ->names('classrooms')
    ->parameters(['turmas' => 'classroom']);

/**
 * Turma PAI (visão geral).
 *
 * GET /turmas/{classroom}
 */
Route::get('/turmas/{classroom}', [ClassroomParentController::class, 'show'])
    ->whereNumber('classroom')
    ->name('classrooms.show');

/**
 * Subturma (child).
 *
 * GET /turmas/{parent}/subturmas/{classroom}
 * Mostra apenas os alunos alocados naquela subturma + oficina.
 *
 * Observação:
 * - Na visão "oficina-first", essa subturma representa um "grupo de oficina".
 */
Route::get('/turmas/{parent}/subturmas/{classroom}', [ClassroomChildController::class, 'show'])
    ->whereNumber('parent')
    ->whereNumber('classroom')
    ->name('subclassrooms.show');

/**
 * Turma PAI > Oficina (sem subturmas)
 *
 * GET /turmas/{classroom}/oficinas/{workshop}
 * Mostra visão da oficina para a turma PAI (quando não há subturmas).
 */
Route::get('/turmas/{classroom}/oficinas/{workshop}', [WorkshopClassController::class, 'show'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.show');

/**
 * Turma PAI > Oficina > Subturmas (lista)
 *
 * GET /turmas/{classroom}/oficinas/{workshop}/subturmas
 */
Route::get('/turmas/{classroom}/oficinas/{workshop}/subturmas', [WorkshopClassController::class, 'indexSubclasses'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.subclasses.index');

/**
 * Turma PAI > Oficina > Subturmas (preview distribuição)
 *
 * GET /turmas/{classroom}/oficinas/{workshop}/preview
 */
Route::get(
    '/turmas/{classroom}/oficinas/{workshop}/preview',
    [WorkshopDistributionController::class, 'preview']
)
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.preview');

/**
 * Turma PAI > Oficina > Subturmas (aplicar distribuição)
 *
 * POST /turmas/{classroom}/oficinas/{workshop}/aplicar
 *
 * Observação:
 * - Aqui entra a lógica de planner/automation de grupos, respeitando travas
 *   (is_locked, dados acadêmicos, etc.) que discutimos.
 */
Route::post(
    '/turmas/{classroom}/oficinas/{workshop}/aplicar',
    [WorkshopDistributionController::class, 'apply']
)
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.apply');

/**
 * Turma PAI > Oficina (ajustar capacidade sem criar subturmas)
 *
 * POST /turmas/{classroom}/oficinas/{workshop}/ajustar-capacidade
 */
Route::post(
    '/turmas/{classroom}/oficinas/{workshop}/ajustar-capacidade',
    [WorkshopDistributionController::class, 'adjustCapacity']
)
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.adjust_capacity');

/*
|--------------------------------------------------------------------------
| 8. Oficinas (catálogo global) - escopo MASTER
|--------------------------------------------------------------------------
|
| Catálogo de tipos de oficina da rede.
| Esse cadastro é global (uma oficina pode ser vinculada a várias escolas).
|
*/

/**
 * Oficinas globais (catálogo)
 * Path base: /oficinas
 */
Route::get('/oficinas', [WorkshopController::class, 'index'])
    ->name('workshops.index');

Route::get('/oficinas/nova', [WorkshopController::class, 'create'])
    ->name('workshops.create');

Route::post('/oficinas', [WorkshopController::class, 'store'])
    ->name('workshops.store');

Route::get('/oficinas/{workshop}/editar', [WorkshopController::class, 'edit'])
    ->whereNumber('workshop')
    ->name('workshops.edit');

Route::put('/oficinas/{workshop}', [WorkshopController::class, 'update'])
    ->whereNumber('workshop')
    ->name('workshops.update');

/*
|--------------------------------------------------------------------------
| 9. Escolas ↔ Oficinas (pivot school_workshop) - escopo MASTER/ESCOLA
|--------------------------------------------------------------------------
|
| Gestão de quais oficinas globais estão ativas em cada escola.
| Hoje o controller é único (SchoolWorkshopController).
|
| No futuro:
| - o MASTER pode gerenciar qualquer escola;
| - a ESCOLA gerencia apenas as próprias oficinas (com RBAC).
|
*/

Route::prefix('escolas/{school}')
    // TODO RBAC ESCOLA/MASTER:
    // ->middleware(['auth', 'can:access-school,school'])
    ->group(function () {
        /**
         * Gestão de oficinas por escola (pivot school_workshop)
         *
         * GET /escolas/{school}/workshops
         * POST /escolas/{school}/workshops
         *
         * Obs.: path em inglês por legado. Se quiser, pode trocar pra /oficinas.
         */
        Route::get('workshops', [SchoolWorkshopController::class, 'edit'])
            ->name('schools.workshops.edit');

        Route::post('workshops', [SchoolWorkshopController::class, 'update'])
            ->name('schools.workshops.update');
    });

/*
|--------------------------------------------------------------------------
| 10. Aulas + Presenças (Lessons) - por Turma/Subturma + Oficina
|--------------------------------------------------------------------------
|
| Esses endpoints são "grupo de oficina" na prática:
| (classroom, workshop) podendo ser turma PAI ou subturma.
|
*/

Route::prefix('turmas/{classroom}/oficinas/{workshop}')
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.lessons.')
    ->group(function () {
        // LISTAR aulas dessa turma/subturma + oficina
        Route::get('aulas', [LessonController::class, 'index'])
            ->name('index');

        // Tela de criação de aula + presença
        Route::get('aulas/criar', [LessonController::class, 'create'])
            ->name('create');

        // Salvar aula + presenças
        Route::post('aulas', [LessonController::class, 'store'])
            ->name('store');

        // Detalhe da aula (presenças)
        Route::get('aulas/{lesson}', [LessonController::class, 'show'])
            ->whereNumber('lesson')
            ->name('show');

        // TODO FUTURO:
        // Rotas de edição de aula:
        // GET  aulas/{lesson}/editar -> lessons.edit
        // PUT  aulas/{lesson}        -> lessons.update
    });

/*
|--------------------------------------------------------------------------
| 11. Avaliações (Assessments) - por Turma/Subturma + Oficina
|--------------------------------------------------------------------------
|
| Mesmo padrão de grupo: (classroom, workshop).
|
*/

Route::prefix('turmas/{classroom}/oficinas/{workshop}')
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.assessments.')
    ->group(function () {
        // Lista avaliações do grupo (turma ou subturma + oficina)
        Route::get('avaliacoes', [AssessmentController::class, 'index'])
            ->name('index');

        // Tela de criação + lançamento de notas
        Route::get('avaliacoes/criar', [AssessmentController::class, 'create'])
            ->name('create');

        // Salvar avaliação + notas
        Route::post('avaliacoes', [AssessmentController::class, 'store'])
            ->name('store');

        // Ver detalhes da avaliação (com notas e stats)
        Route::get('avaliacoes/{assessment}', [AssessmentController::class, 'show'])
            ->whereNumber('assessment')
            ->name('show');
    });

/*
|--------------------------------------------------------------------------
| 12. Perfil / Autenticação (perfil do usuário)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    /**
     * Perfil do usuário autenticado.
     */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 13. Rotas ESCOLA (escopo escola) - visão interna da escola
|--------------------------------------------------------------------------
|
| Aqui entra o "segundo menu" (navbar da escola).
| Tudo é aninhado em /escolas/{school}/...
|
| Ideia:
| - MASTER também pode acessar (auditar, apoiar).
| - Usuários da escola só podem acessar a própria escola.
|
| TODO RBAC ESCOLA:
| - Quando implementar:
|   -> adicionar middleware(['auth', 'can:access-school,school'])
|
*/

Route::prefix('escolas/{school}')
    ->whereNumber('school')
    ->name('schools.')
    ->group(function () {
        /*
         * IMPORTANTE:
         * - Não redefinimos schools.show aqui para evitar conflito,
         *   pois o resource('escolas', SchoolController::class) já cuida disso.
         * - Usamos esse prefixo apenas para sub-recursos da escola.
         */

        /**
         * Alunos da ESCOLA (escopo escola)
         *
         * Path: /escolas/{school}/alunos
         * Name: schools.students.*
         *
         * Controller sugerido:
         * - App\Http\Controllers\Schools\SchoolStudentController
         *   -> index: lista alunos da escola
         *   -> create/store: criar aluno já vinculado à escola
         *   -> show/edit/update: operações dentro do contexto da escola
         */
        Route::resource('alunos', SchoolStudentController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
            ->names('students')
            ->parameters(['alunos' => 'student']);

        /**
         * Matrículas da ESCOLA (StudentEnrollment no contexto da escola)
         *
         * Path: /escolas/{school}/matriculas
         * Name: schools.enrollments.*
         *
         * Controller sugerido:
         * - App\Http\Controllers\Schools\SchoolEnrollmentController
         *
         * Observação:
         * - Aqui você pode permitir criar episódios de matrícula novos
         *   (transferência, rematrícula, etc.) no contexto da escola.
         */
        Route::resource('matriculas', SchoolEnrollmentController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
            ->names('enrollments')
            ->parameters(['matriculas' => 'enrollment']);

        /**
         * Professores da ESCOLA
         *
         * Path: /escolas/{school}/professores
         * Name: schools.teachers.*
         *
         * Controller sugerido:
         * - App\Http\Controllers\Schools\SchoolTeacherController
         *
         * Por enquanto, pode ser só index/show de professores vinculados à escola.
         */
        Route::resource('professores', SchoolTeacherController::class)
            ->only(['index', 'show'])
            ->names('teachers')
            ->parameters(['professores' => 'teacher']);

        /**
         * Grupos / Turmas da ESCOLA
         *
         * Path: /escolas/{school}/grupos
         * Name: schools.classrooms.* (ou schools.groups.* se preferir)
         *
         * Controller sugerido:
         * - App\Http\Controllers\Schools\SchoolClassroomController
         *
         * Ideia:
         * - Mostrar "grupos de oficina" da escola (independente de ser PAI/child),
         *   com foco na visão prática da escola, não no modelo interno.
         */
        Route::resource('grupos', SchoolClassroomController::class)
            ->only(['index', 'show'])
            ->names('classrooms')
            ->parameters(['grupos' => 'classroom']);

        /**
         * Relatórios da ESCOLA (dashboard de relatórios)
         *
         * Path: /escolas/{school}/relatorios
         * Name: schools.reports.index
         *
         * Controller sugerido:
         * - App\Http\Controllers\Reports\SchoolReportsController
         *
         * Pode ser uma tela com links para:
         * - Relatório por ano escolar
         * - Relatório de frequência por oficina/grupo
         * - etc.
         */
        Route::get('relatorios', [SchoolReportsController::class, 'index'])
            ->name('reports.index');
        Route::get('relatorios/grupos', [SchoolGroupsOverviewReportController::class, 'index'])
            ->name('reports.groups.index');

        Route::get('relatorios/oficinas', [SchoolWorkshopsOverviewReportController::class, 'index'])
            ->name('reports.workshops.index');

        Route::get('relatorios/oficinas/capacidade', [SchoolWorkshopCapacityReportController::class, 'index'])
            ->name('reports.workshops.capacity');

        Route::get('relatorios/alunos/nao-alocados', [SchoolUnallocatedStudentsReportController::class, 'index'])
            ->name('reports.students.unallocated');
        /**
         * Relatório: Alunos por Ano Escolar (já existia)
         *
         * Path: /escolas/{school}/anos-escolares/{gradeLevel}/alunos
         * Name: schools.grade-level-students.index
         *
         * Controller:
         * - App\Http\Controllers\Reports\SchoolGradeLevelStudentsController
         *
         * Já implementado: lista alunos da escola naquele ano com espaço
         * para médias de nota e frequência.
         */
        Route::get('anos-escolares/{gradeLevel}/alunos', [SchoolGradeLevelStudentsController::class, 'index'])
            ->whereNumber('gradeLevel')
            ->name('grade-level-students.index');
    });
