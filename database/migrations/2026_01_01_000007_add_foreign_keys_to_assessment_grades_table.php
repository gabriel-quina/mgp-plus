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

            Schema::create('assessment_grades__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->index();
                $table->foreignId('student_enrollment_id')->index();
                $table->decimal('score_points', 5, 2)->nullable();
                $table->string('score_concept', 20)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['assessment_id', 'student_enrollment_id']);

                $table->foreign('assessment_id')
                    ->references('id')
                    ->on('assessments')
                    ->cascadeOnDelete();
                $table->foreign('student_enrollment_id')
                    ->references('id')
                    ->on('student_enrollments')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO assessment_grades__tmp (
                    id, assessment_id, student_enrollment_id,
                    score_points, score_concept, notes,
                    created_at, updated_at
                )
                SELECT
                    id, assessment_id, student_enrollment_id,
                    score_points, score_concept, notes,
                    created_at, updated_at
                FROM assessment_grades
            ");

            Schema::drop('assessment_grades');
            Schema::rename('assessment_grades__tmp', 'assessment_grades');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('assessment_grades', function (Blueprint $table) {
            $table->foreign('assessment_id')
                ->references('id')
                ->on('assessments')
                ->cascadeOnDelete();
            $table->foreign('student_enrollment_id')
                ->references('id')
                ->on('student_enrollments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('assessment_grades__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_id')->index();
                $table->foreignId('student_enrollment_id')->index();
                $table->decimal('score_points', 5, 2)->nullable();
                $table->string('score_concept', 20)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['assessment_id', 'student_enrollment_id']);
            });

            DB::statement("
                INSERT INTO assessment_grades__tmp (
                    id, assessment_id, student_enrollment_id,
                    score_points, score_concept, notes,
                    created_at, updated_at
                )
                SELECT
                    id, assessment_id, student_enrollment_id,
                    score_points, score_concept, notes,
                    created_at, updated_at
                FROM assessment_grades
            ");

            Schema::drop('assessment_grades');
            Schema::rename('assessment_grades__tmp', 'assessment_grades');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('assessment_grades', function (Blueprint $table) {
            $table->dropForeign(['assessment_id']);
            $table->dropForeign(['student_enrollment_id']);
        });
    }
};
