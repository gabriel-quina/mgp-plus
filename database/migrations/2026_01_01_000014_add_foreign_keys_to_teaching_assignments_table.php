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

            Schema::create('teaching_assignments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->index();
                $table->foreignId('school_id')->index();
                $table->foreignId('engagement_id')->nullable()->index();
                $table->unsignedSmallInteger('academic_year');
                $table->string('shift', 16)->nullable();
                $table->unsignedTinyInteger('hours_per_week')->nullable();
                $table->string('notes', 500)->nullable();
                $table->timestamps();

                $table->unique(['teacher_id', 'school_id', 'academic_year', 'shift']);
                $table->index(['teacher_id', 'academic_year']);
                $table->index('school_id');

                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('teachers')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
                $table->foreign('engagement_id')
                    ->references('id')
                    ->on('teacher_engagements')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });

            DB::statement("
                INSERT INTO teaching_assignments__tmp (
                    id, teacher_id, school_id, engagement_id,
                    academic_year, shift, hours_per_week, notes,
                    created_at, updated_at
                )
                SELECT
                    id, teacher_id, school_id, engagement_id,
                    academic_year, shift, hours_per_week, notes,
                    created_at, updated_at
                FROM teaching_assignments
            ");

            Schema::drop('teaching_assignments');
            Schema::rename('teaching_assignments__tmp', 'teaching_assignments');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('engagement_id')
                ->references('id')
                ->on('teacher_engagements')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('teaching_assignments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->index();
                $table->foreignId('school_id')->index();
                $table->foreignId('engagement_id')->nullable()->index();
                $table->unsignedSmallInteger('academic_year');
                $table->string('shift', 16)->nullable();
                $table->unsignedTinyInteger('hours_per_week')->nullable();
                $table->string('notes', 500)->nullable();
                $table->timestamps();

                $table->unique(['teacher_id', 'school_id', 'academic_year', 'shift']);
                $table->index(['teacher_id', 'academic_year']);
                $table->index('school_id');
            });

            DB::statement("
                INSERT INTO teaching_assignments__tmp (
                    id, teacher_id, school_id, engagement_id,
                    academic_year, shift, hours_per_week, notes,
                    created_at, updated_at
                )
                SELECT
                    id, teacher_id, school_id, engagement_id,
                    academic_year, shift, hours_per_week, notes,
                    created_at, updated_at
                FROM teaching_assignments
            ");

            Schema::drop('teaching_assignments');
            Schema::rename('teaching_assignments__tmp', 'teaching_assignments');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['school_id']);
            $table->dropForeign(['engagement_id']);
        });
    }
};
