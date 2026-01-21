<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->unique('uf', 'uq_states_uf');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->index('state_id', 'idx_cities_state_id');
            $table->unique(['state_id', 'name'], 'uq_cities_state_name');

            $table->foreign('state_id', 'fk_cities_state_id')
                ->references('id')->on('states')
                ->cascadeOnDelete();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->index('city_id', 'idx_schools_city_id');
            $table->index('administrative_dependency', 'idx_schools_admin_dep');

            $table->foreign('city_id', 'fk_schools_city_id')
                ->references('id')->on('cities')
                ->restrictOnDelete();
        });

        Schema::table('grade_levels', function (Blueprint $table) {
            $table->index('sequence', 'idx_grade_levels_sequence');
            $table->index('is_active', 'idx_grade_levels_is_active');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->index('is_active', 'idx_workshops_is_active');
        });

        Schema::table('school_workshops', function (Blueprint $table) {
            $table->index('school_id', 'idx_school_workshops_school_id');
            $table->index('workshop_id', 'idx_school_workshops_workshop_id');
            $table->index('status', 'idx_school_workshops_status');
            $table->index(['school_id', 'workshop_id'], 'idx_school_workshops_school_workshop');
            $table->index(['starts_at', 'ends_at'], 'idx_school_workshops_dates');

            $table->foreign('school_id', 'fk_school_workshops_school_id')
                ->references('id')->on('schools')
                ->cascadeOnDelete();

            $table->foreign('workshop_id', 'fk_school_workshops_workshop_id')
                ->references('id')->on('workshops')
                ->restrictOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unique('cpf', 'uq_students_cpf');
            $table->index('name', 'idx_students_name');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->unique('cpf', 'uq_teachers_cpf');
            $table->index('is_active', 'idx_teachers_is_active');
            $table->index('name', 'idx_teachers_name');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->index('student_id', 'idx_student_enrollments_student_id');
            $table->index('school_id', 'idx_student_enrollments_school_id');
            $table->index('grade_level_id', 'idx_student_enrollments_grade_level_id');
            $table->index('academic_year', 'idx_student_enrollments_academic_year');
            $table->index('shift', 'idx_student_enrollments_shift');
            $table->index('status', 'idx_student_enrollments_status');

            $table->index(
                ['school_id', 'academic_year', 'shift', 'status'],
                'idx_student_enrollments_school_year_shift_status'
            );

            $table->foreign('student_id', 'fk_student_enrollments_student_id')
                ->references('id')->on('students')
                ->cascadeOnDelete();

            $table->foreign('school_id', 'fk_student_enrollments_school_id')
                ->references('id')->on('schools')
                ->restrictOnDelete();

            $table->foreign('grade_level_id', 'fk_student_enrollments_grade_level_id')
                ->references('id')->on('grade_levels')
                ->restrictOnDelete();

            $table->foreign('origin_school_id', 'fk_student_enrollments_origin_school_id')
                ->references('id')->on('schools')
                ->nullOnDelete();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->index('school_id', 'idx_classrooms_school_id');
            $table->index('school_workshop_id', 'idx_classrooms_school_workshop_id');
            $table->index(['school_id', 'academic_year', 'shift'], 'idx_classrooms_school_year_shift');

            $table->unique(
                ['school_id', 'academic_year', 'shift', 'school_workshop_id', 'grades_signature', 'group_number'],
                'uq_classrooms_identity'
            );

            $table->foreign('school_id', 'fk_classrooms_school_id')
                ->references('id')->on('schools')
                ->restrictOnDelete();

            $table->foreign('school_workshop_id', 'fk_classrooms_school_workshop_id')
                ->references('id')->on('school_workshops')
                ->restrictOnDelete();
        });

        Schema::table('classroom_grade_level', function (Blueprint $table) {
            $table->unique(['classroom_id', 'grade_level_id'], 'uq_classroom_grade_level_pair');
            $table->index('grade_level_id', 'idx_classroom_grade_level_grade_level_id');

            $table->foreign('classroom_id', 'fk_classroom_grade_level_classroom_id')
                ->references('id')->on('classrooms')
                ->cascadeOnDelete();

            $table->foreign('grade_level_id', 'fk_classroom_grade_level_grade_level_id')
                ->references('id')->on('grade_levels')
                ->restrictOnDelete();
        });

        Schema::table('classroom_memberships', function (Blueprint $table) {
            $table->index('classroom_id', 'idx_classroom_memberships_classroom_id');
            $table->index('student_enrollment_id', 'idx_classroom_memberships_enrollment_id');

            $table->index(['classroom_id', 'starts_at', 'ends_at'], 'idx_classroom_memberships_classroom_activeat');
            $table->index(['student_enrollment_id', 'starts_at', 'ends_at'], 'idx_classroom_memberships_enrollment_activeat');

            $table->foreign('classroom_id', 'fk_classroom_memberships_classroom_id')
                ->references('id')->on('classrooms')
                ->cascadeOnDelete();

            $table->foreign('student_enrollment_id', 'fk_classroom_memberships_enrollment_id')
                ->references('id')->on('student_enrollments')
                ->cascadeOnDelete();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->index('teacher_id', 'idx_lessons_teacher_id');
            $table->index(['classroom_id', 'taught_at'], 'idx_lessons_classroom_taught_at');

            $table->foreign('classroom_id', 'fk_lessons_classroom_id')
                ->references('id')->on('classrooms')
                ->cascadeOnDelete();

            $table->foreign('teacher_id', 'fk_lessons_teacher_id')
                ->references('id')->on('teachers')
                ->restrictOnDelete();
        });

        Schema::table('lesson_attendances', function (Blueprint $table) {
            $table->unique(['lesson_id', 'student_enrollment_id'], 'uq_lesson_attendances_pair');
            $table->index('student_enrollment_id', 'idx_lesson_attendances_enrollment_id');

            $table->foreign('lesson_id', 'fk_lesson_attendances_lesson_id')
                ->references('id')->on('lessons')
                ->cascadeOnDelete();

            $table->foreign('student_enrollment_id', 'fk_lesson_attendances_enrollment_id')
                ->references('id')->on('student_enrollments')
                ->cascadeOnDelete();
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->index(['classroom_id', 'due_at'], 'idx_assessments_classroom_due_at');

            $table->foreign('classroom_id', 'fk_assessments_classroom_id')
                ->references('id')->on('classrooms')
                ->cascadeOnDelete();
        });

        Schema::table('assessment_grades', function (Blueprint $table) {
            $table->unique(['assessment_id', 'student_enrollment_id'], 'uq_assessment_grades_pair');
            $table->index('student_enrollment_id', 'idx_assessment_grades_enrollment_id');

            $table->foreign('assessment_id', 'fk_assessment_grades_assessment_id')
                ->references('id')->on('assessments')
                ->cascadeOnDelete();

            $table->foreign('student_enrollment_id', 'fk_assessment_grades_enrollment_id')
                ->references('id')->on('student_enrollments')
                ->cascadeOnDelete();
        });

        Schema::table('teacher_engagements', function (Blueprint $table) {
            $table->index('teacher_id', 'idx_teacher_engagements_teacher_id');
            $table->index('city_id', 'idx_teacher_engagements_city_id');
            $table->index('engagement_type', 'idx_teacher_engagements_type');

            $table->foreign('teacher_id', 'fk_teacher_engagements_teacher_id')
                ->references('id')->on('teachers')
                ->cascadeOnDelete();

            $table->foreign('city_id', 'fk_teacher_engagements_city_id')
                ->references('id')->on('cities')
                ->nullOnDelete();
        });

        Schema::table('teacher_city_access', function (Blueprint $table) {
            $table->unique(['teacher_id', 'city_id'], 'uq_teacher_city_access_pair');
            $table->index('city_id', 'idx_teacher_city_access_city_id');

            $table->foreign('teacher_id', 'fk_teacher_city_access_teacher_id')
                ->references('id')->on('teachers')
                ->cascadeOnDelete();

            $table->foreign('city_id', 'fk_teacher_city_access_city_id')
                ->references('id')->on('cities')
                ->cascadeOnDelete();
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->unique(['teacher_id', 'school_id', 'academic_year'], 'uq_teaching_assignments_teacher_school_year');
            $table->index(['school_id', 'academic_year'], 'idx_teaching_assignments_school_year');
            $table->index('engagement_id', 'idx_teaching_assignments_engagement_id');

            $table->foreign('teacher_id', 'fk_teaching_assignments_teacher_id')
                ->references('id')->on('teachers')
                ->cascadeOnDelete();

            $table->foreign('school_id', 'fk_teaching_assignments_school_id')
                ->references('id')->on('schools')
                ->cascadeOnDelete();

            $table->foreign('engagement_id', 'fk_teaching_assignments_engagement_id')
                ->references('id')->on('teacher_engagements')
                ->nullOnDelete();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->unique('name', 'uq_roles_name');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->unique('name', 'uq_permissions_name');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->unique(['permission_id', 'role_id'], 'uq_permission_role_pair');
            $table->index('role_id', 'idx_permission_role_role_id');

            $table->foreign('permission_id', 'fk_permission_role_permission_id')
                ->references('id')->on('permissions')
                ->cascadeOnDelete();

            $table->foreign('role_id', 'fk_permission_role_role_id')
                ->references('id')->on('roles')
                ->cascadeOnDelete();
        });

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->index('user_id', 'idx_role_assignments_user_id');
            $table->index('role_id', 'idx_role_assignments_role_id');
            $table->index(['scope_type', 'scope_id'], 'idx_role_assignments_scope');

            $table->foreign('user_id', 'fk_role_assignments_user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            $table->foreign('role_id', 'fk_role_assignments_role_id')
                ->references('id')->on('roles')
                ->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email', 'uq_users_email');
            $table->unique('cpf', 'uq_users_cpf');
            $table->index('is_master', 'idx_users_is_master');
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_email_or_cpf CHECK (email IS NOT NULL OR cpf IS NOT NULL)");

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_sessions_user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign('fk_sessions_user_id');
        });

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->dropForeign('fk_role_assignments_user_id');
            $table->dropForeign('fk_role_assignments_role_id');

            $table->dropIndex('idx_role_assignments_user_id');
            $table->dropIndex('idx_role_assignments_role_id');
            $table->dropIndex('idx_role_assignments_scope');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropForeign('fk_permission_role_permission_id');
            $table->dropForeign('fk_permission_role_role_id');

            $table->dropUnique('uq_permission_role_pair');
            $table->dropIndex('idx_permission_role_role_id');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique('uq_permissions_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('uq_roles_name');
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropForeign('fk_teaching_assignments_teacher_id');
            $table->dropForeign('fk_teaching_assignments_school_id');
            $table->dropForeign('fk_teaching_assignments_engagement_id');

            $table->dropUnique('uq_teaching_assignments_teacher_school_year');
            $table->dropIndex('idx_teaching_assignments_school_year');
            $table->dropIndex('idx_teaching_assignments_engagement_id');
        });

        Schema::table('teacher_city_access', function (Blueprint $table) {
            $table->dropForeign('fk_teacher_city_access_teacher_id');
            $table->dropForeign('fk_teacher_city_access_city_id');

            $table->dropUnique('uq_teacher_city_access_pair');
            $table->dropIndex('idx_teacher_city_access_city_id');
        });

        Schema::table('teacher_engagements', function (Blueprint $table) {
            $table->dropForeign('fk_teacher_engagements_teacher_id');
            $table->dropForeign('fk_teacher_engagements_city_id');

            $table->dropIndex('idx_teacher_engagements_teacher_id');
            $table->dropIndex('idx_teacher_engagements_city_id');
            $table->dropIndex('idx_teacher_engagements_type');
        });

        Schema::table('assessment_grades', function (Blueprint $table) {
            $table->dropForeign('fk_assessment_grades_assessment_id');
            $table->dropForeign('fk_assessment_grades_enrollment_id');

            $table->dropUnique('uq_assessment_grades_pair');
            $table->dropIndex('idx_assessment_grades_enrollment_id');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign('fk_assessments_classroom_id');
            $table->dropIndex('idx_assessments_classroom_due_at');
        });

        Schema::table('lesson_attendances', function (Blueprint $table) {
            $table->dropForeign('fk_lesson_attendances_lesson_id');
            $table->dropForeign('fk_lesson_attendances_enrollment_id');

            $table->dropUnique('uq_lesson_attendances_pair');
            $table->dropIndex('idx_lesson_attendances_enrollment_id');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign('fk_lessons_classroom_id');
            $table->dropForeign('fk_lessons_teacher_id');

            $table->dropIndex('idx_lessons_teacher_id');
            $table->dropIndex('idx_lessons_classroom_taught_at');
        });

        Schema::table('classroom_memberships', function (Blueprint $table) {
            $table->dropForeign('fk_classroom_memberships_classroom_id');
            $table->dropForeign('fk_classroom_memberships_enrollment_id');

            $table->dropIndex('idx_classroom_memberships_classroom_id');
            $table->dropIndex('idx_classroom_memberships_enrollment_id');
            $table->dropIndex('idx_classroom_memberships_classroom_activeat');
            $table->dropIndex('idx_classroom_memberships_enrollment_activeat');
        });

        Schema::table('classroom_grade_level', function (Blueprint $table) {
            $table->dropForeign('fk_classroom_grade_level_classroom_id');
            $table->dropForeign('fk_classroom_grade_level_grade_level_id');

            $table->dropUnique('uq_classroom_grade_level_pair');
            $table->dropIndex('idx_classroom_grade_level_grade_level_id');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign('fk_classrooms_school_id');
            $table->dropForeign('fk_classrooms_school_workshop_id');

            $table->dropUnique('uq_classrooms_identity');
            $table->dropIndex('idx_classrooms_school_id');
            $table->dropIndex('idx_classrooms_school_workshop_id');
            $table->dropIndex('idx_classrooms_school_year_shift');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign('fk_student_enrollments_student_id');
            $table->dropForeign('fk_student_enrollments_school_id');
            $table->dropForeign('fk_student_enrollments_grade_level_id');
            $table->dropForeign('fk_student_enrollments_origin_school_id');

            $table->dropIndex('idx_student_enrollments_student_id');
            $table->dropIndex('idx_student_enrollments_school_id');
            $table->dropIndex('idx_student_enrollments_grade_level_id');
            $table->dropIndex('idx_student_enrollments_academic_year');
            $table->dropIndex('idx_student_enrollments_shift');
            $table->dropIndex('idx_student_enrollments_status');
            $table->dropIndex('idx_student_enrollments_school_year_shift_status');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique('uq_teachers_cpf');
            $table->dropIndex('idx_teachers_is_active');
            $table->dropIndex('idx_teachers_name');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('uq_students_cpf');
            $table->dropIndex('idx_students_name');
        });

        Schema::table('school_workshops', function (Blueprint $table) {
            $table->dropForeign('fk_school_workshops_school_id');
            $table->dropForeign('fk_school_workshops_workshop_id');

            $table->dropIndex('idx_school_workshops_school_id');
            $table->dropIndex('idx_school_workshops_workshop_id');
            $table->dropIndex('idx_school_workshops_status');
            $table->dropIndex('idx_school_workshops_school_workshop');
            $table->dropIndex('idx_school_workshops_dates');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropIndex('idx_workshops_is_active');
        });

        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropIndex('idx_grade_levels_sequence');
            $table->dropIndex('idx_grade_levels_is_active');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign('fk_schools_city_id');

            $table->dropIndex('idx_schools_city_id');
            $table->dropIndex('idx_schools_admin_dep');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign('fk_cities_state_id');

            $table->dropIndex('idx_cities_state_id');
            $table->dropUnique('uq_cities_state_name');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->dropUnique('uq_states_uf');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('uq_users_email');
            $table->dropUnique('uq_users_cpf');
            $table->dropIndex('idx_users_is_master');
        });
    }
};

