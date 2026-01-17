<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('student_enrollments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->index();
                $table->foreignId('school_id')->index();
                $table->foreignId('grade_level_id')->index();
                $table->unsignedSmallInteger('academic_year');
                $table->enum('shift', ['morning', 'afternoon', 'evening'])->default('morning');
                $table->enum('transfer_scope', ['first', 'internal', 'external'])->default('first');
                $table->foreignId('origin_school_id')->nullable()->index('idx_se_origin_school');
                $table->date('started_at')->nullable();
                $table->date('ended_at')->nullable();
                $table->enum('status', [
                    'pre_enrolled',
                    'enrolled',
                    'active',
                    'completed',
                    'failed',
                    'transferred',
                    'dropped',
                    'suspended',
                ])->default('active');
                $table->timestamps();

                $table->index(['student_id', 'status'], 'idx_se_student_status');
                $table->index(['school_id', 'academic_year', 'shift', 'status'], 'idx_se_school_year_shift_status');
                $table->foreign('student_id')
                    ->references('id')
                    ->on('students')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('grade_level_id')
                    ->references('id')
                    ->on('grade_levels')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('origin_school_id')
                    ->references('id')
                    ->on('schools')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });

            DB::statement("
                INSERT INTO student_enrollments__tmp (
                    id, student_id, school_id, grade_level_id,
                    academic_year, shift, transfer_scope, origin_school_id,
                    started_at, ended_at, status,
                    created_at, updated_at
                )
                SELECT
                    id, student_id, school_id, grade_level_id,
                    academic_year, shift, transfer_scope, origin_school_id,
                    started_at, ended_at, status,
                    created_at, updated_at
                FROM student_enrollments
            ");

            Schema::drop('student_enrollments');
            Schema::rename('student_enrollments__tmp', 'student_enrollments');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('grade_level_id')
                ->references('id')
                ->on('grade_levels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('origin_school_id')
                ->references('id')
                ->on('schools')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('student_enrollments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->index();
                $table->foreignId('school_id')->index();
                $table->foreignId('grade_level_id')->index();
                $table->unsignedSmallInteger('academic_year');
                $table->enum('shift', ['morning', 'afternoon', 'evening'])->default('morning');
                $table->enum('transfer_scope', ['first', 'internal', 'external'])->default('first');
                $table->foreignId('origin_school_id')->nullable()->index('idx_se_origin_school');
                $table->date('started_at')->nullable();
                $table->date('ended_at')->nullable();
                $table->enum('status', [
                    'pre_enrolled',
                    'enrolled',
                    'active',
                    'completed',
                    'failed',
                    'transferred',
                    'dropped',
                    'suspended',
                ])->default('active');
                $table->timestamps();

                $table->index(['student_id', 'status'], 'idx_se_student_status');
                $table->index(['school_id', 'academic_year', 'shift', 'status'], 'idx_se_school_year_shift_status');
            });

            DB::statement("
                INSERT INTO student_enrollments__tmp (
                    id, student_id, school_id, grade_level_id,
                    academic_year, shift, transfer_scope, origin_school_id,
                    started_at, ended_at, status,
                    created_at, updated_at
                )
                SELECT
                    id, student_id, school_id, grade_level_id,
                    academic_year, shift, transfer_scope, origin_school_id,
                    started_at, ended_at, status,
                    created_at, updated_at
                FROM student_enrollments
            ");

            Schema::drop('student_enrollments');
            Schema::rename('student_enrollments__tmp', 'student_enrollments');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_id']);
            $table->dropForeign(['grade_level_id']);
            $table->dropForeign(['origin_school_id']);
        });
    }
};
