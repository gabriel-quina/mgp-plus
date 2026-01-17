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

            Schema::create('workshop_allocations__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('child_classroom_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->foreignId('student_enrollment_id')->index();
                $table->boolean('is_locked')->default(false);
                $table->string('note', 300)->nullable();
                $table->timestamps();

                $table->unique(
                    ['child_classroom_id', 'workshop_id', 'student_enrollment_id'],
                    'wk_alloc_unique_in_child'
                );
                $table->unique(
                    ['workshop_id', 'student_enrollment_id'],
                    'wk_alloc_unique_per_workshop'
                );

                $table->foreign('child_classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('student_enrollment_id')
                    ->references('id')
                    ->on('student_enrollments')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO workshop_allocations__tmp (
                    id, child_classroom_id, workshop_id, student_enrollment_id,
                    is_locked, note, created_at, updated_at
                )
                SELECT
                    id, child_classroom_id, workshop_id, student_enrollment_id,
                    is_locked, note, created_at, updated_at
                FROM workshop_allocations
            ");

            Schema::drop('workshop_allocations');
            Schema::rename('workshop_allocations__tmp', 'workshop_allocations');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('workshop_allocations', function (Blueprint $table) {
            $table->foreign('child_classroom_id')
                ->references('id')
                ->on('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('workshop_id')
                ->references('id')
                ->on('workshops')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('student_enrollment_id')
                ->references('id')
                ->on('student_enrollments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('workshop_allocations__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('child_classroom_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->foreignId('student_enrollment_id')->index();
                $table->boolean('is_locked')->default(false);
                $table->string('note', 300)->nullable();
                $table->timestamps();

                $table->unique(
                    ['child_classroom_id', 'workshop_id', 'student_enrollment_id'],
                    'wk_alloc_unique_in_child'
                );
                $table->unique(
                    ['workshop_id', 'student_enrollment_id'],
                    'wk_alloc_unique_per_workshop'
                );
            });

            DB::statement("
                INSERT INTO workshop_allocations__tmp (
                    id, child_classroom_id, workshop_id, student_enrollment_id,
                    is_locked, note, created_at, updated_at
                )
                SELECT
                    id, child_classroom_id, workshop_id, student_enrollment_id,
                    is_locked, note, created_at, updated_at
                FROM workshop_allocations
            ");

            Schema::drop('workshop_allocations');
            Schema::rename('workshop_allocations__tmp', 'workshop_allocations');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('workshop_allocations', function (Blueprint $table) {
            $table->dropForeign(['child_classroom_id']);
            $table->dropForeign(['workshop_id']);
            $table->dropForeign(['student_enrollment_id']);
        });
    }
};
