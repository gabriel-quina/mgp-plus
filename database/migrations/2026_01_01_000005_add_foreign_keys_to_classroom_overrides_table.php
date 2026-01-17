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

            Schema::create('classroom_overrides__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_enrollment_id')->index();
                $table->foreignId('from_classroom_id')->index();
                $table->foreignId('to_classroom_id')->index();
                $table->boolean('is_active')->default(true);
                $table->string('reason', 300)->nullable();
                $table->timestamps();

                $table->unique(
                    ['student_enrollment_id', 'is_active'],
                    'one_active_override_per_year'
                );

                $table->foreign('student_enrollment_id')
                    ->references('id')
                    ->on('student_enrollments')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('from_classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('to_classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO classroom_overrides__tmp (
                    id, student_enrollment_id, from_classroom_id, to_classroom_id,
                    is_active, reason, created_at, updated_at
                )
                SELECT
                    id, student_enrollment_id, from_classroom_id, to_classroom_id,
                    is_active, reason, created_at, updated_at
                FROM classroom_overrides
            ");

            Schema::drop('classroom_overrides');
            Schema::rename('classroom_overrides__tmp', 'classroom_overrides');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classroom_overrides', function (Blueprint $table) {
            $table->foreign('student_enrollment_id')
                ->references('id')
                ->on('student_enrollments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('from_classroom_id')
                ->references('id')
                ->on('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('to_classroom_id')
                ->references('id')
                ->on('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('classroom_overrides__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_enrollment_id')->index();
                $table->foreignId('from_classroom_id')->index();
                $table->foreignId('to_classroom_id')->index();
                $table->boolean('is_active')->default(true);
                $table->string('reason', 300)->nullable();
                $table->timestamps();

                $table->unique(
                    ['student_enrollment_id', 'is_active'],
                    'one_active_override_per_year'
                );
            });

            DB::statement("
                INSERT INTO classroom_overrides__tmp (
                    id, student_enrollment_id, from_classroom_id, to_classroom_id,
                    is_active, reason, created_at, updated_at
                )
                SELECT
                    id, student_enrollment_id, from_classroom_id, to_classroom_id,
                    is_active, reason, created_at, updated_at
                FROM classroom_overrides
            ");

            Schema::drop('classroom_overrides');
            Schema::rename('classroom_overrides__tmp', 'classroom_overrides');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classroom_overrides', function (Blueprint $table) {
            $table->dropForeign(['student_enrollment_id']);
            $table->dropForeign(['from_classroom_id']);
            $table->dropForeign(['to_classroom_id']);
        });
    }
};
