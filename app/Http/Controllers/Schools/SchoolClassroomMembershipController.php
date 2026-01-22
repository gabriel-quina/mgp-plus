<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class SchoolClassroomMembershipController extends Controller
{
    public function index(Request $request, School $school, Classroom $classroom)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $at = $request->query('at') ? now()->parse($request->query('at')) : now();

        $classroom->loadMissing(['gradeLevels', 'schoolWorkshop.workshop']);

        $gradeIds = $classroom->gradeLevels->pluck('id')->all();

        // Alunos no grupo (ativos em $at)
        $activeMemberships = ClassroomMembership::query()
            ->where('classroom_id', $classroom->id)
            ->activeAt($at)
            ->with(['enrollment.student', 'enrollment.gradeLevel'])
            ->orderBy('starts_at')
            ->get();

        // Elegíveis: enrollments da escola nas séries do grupo
        $eligibleEnrollments = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->whereIn('grade_level_id', $gradeIds)
            ->with(['student', 'gradeLevel'])
            ->orderBy('grade_level_id')
            ->orderBy('id')
            ->get();

        // Mapa de membership ativa (em qualquer grupo) por enrollment
        $activeByEnrollmentId = ClassroomMembership::query()
            ->activeAt($at)
            ->whereIn('student_enrollment_id', $eligibleEnrollments->pluck('id'))
            ->with(['classroom.schoolWorkshop.workshop', 'classroom.gradeLevels'])
            ->get()
            ->keyBy('student_enrollment_id');

        return view('schools.classrooms.memberships.index', [
            'school' => $school,
            'classroom' => $classroom,
            'at' => $at,
            'activeMemberships' => $activeMemberships,
            'eligibleEnrollments' => $eligibleEnrollments,
            'activeByEnrollmentId' => $activeByEnrollmentId,
        ]);
    }

    public function store(Request $request, School $school, Classroom $classroom)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $data = $request->validate([
            'student_enrollment_id' => ['required', 'integer'],
            'starts_at' => ['nullable', 'date'],
        ]);

        $classroom->loadMissing('gradeLevels');
        $gradeIds = $classroom->gradeLevels->pluck('id')->all();

        $enrollment = StudentEnrollment::query()
            ->whereKey((int) $data['student_enrollment_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        // Garante compatibilidade por série
        abort_unless(in_array((int) $enrollment->grade_level_id, array_map('intval', $gradeIds), true), 422);

        $startsAt = isset($data['starts_at']) && $data['starts_at']
            ? now()->parse($data['starts_at'])->startOfDay()
            : now();

        ClassroomMembership::create([
            'student_enrollment_id' => $enrollment->id,
            'classroom_id' => $classroom->id,
            'starts_at' => $startsAt,
            'ends_at' => null,
        ]);

        return redirect()
            ->route('schools.classrooms.memberships.index', [$school, $classroom])
            ->with('success', 'Aluno alocado/movido para o grupo.');
    }

    public function end(Request $request, School $school, Classroom $classroom, ClassroomMembership $membership)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);
        abort_unless((int) $membership->classroom_id === (int) $classroom->id, 404);

        $data = $request->validate([
            'ends_at' => ['nullable', 'date'],
        ]);

        $endsAt = isset($data['ends_at']) && $data['ends_at']
            ? now()->parse($data['ends_at'])->startOfDay()
            : now();

        $membership->forceFill(['ends_at' => $endsAt])->save();

        return redirect()
            ->route('schools.classrooms.memberships.index', [$school, $classroom])
            ->with('success', 'Membership encerrada.');
    }
}

