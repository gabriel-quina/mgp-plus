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
            DB::statement('DROP INDEX IF EXISTS uniq_class_school_year_shift_set');

            Schema::create('classrooms__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->index();
                $table->unsignedSmallInteger('academic_year')->nullable();
                $table->foreignId('parent_classroom_id')->nullable()->index();
                $table->foreignId('workshop_id')->nullable()->index();
                $table->foreignId('workshop_group_set_id')->nullable()->index();
                $table->unsignedInteger('group_number')->nullable();
                $table->string('name', 150);
                $table->enum('shift', ['morning','afternoon','evening']);
                $table->boolean('is_active')->default(true);
                $table->string('grade_level_key', 191)->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamp('locked_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['school_id', 'academic_year', 'shift', 'grade_level_key'],
                    'uniq_class_school_year_shift_set'
                );

                $table->index(['school_id', 'academic_year'], 'idx_class_school_year');
                $table->index(['shift', 'is_active'], 'idx_class_shift_active');
                $table->index(['workshop_group_set_id', 'group_number'], 'idx_classroom_group_set_number');

                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('parent_classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->nullOnDelete();
                $table->foreign('workshop_group_set_id')
                    ->references('id')
                    ->on('workshop_group_sets')
                    ->nullOnDelete();
            });

            DB::statement("
                INSERT INTO classrooms__tmp (
                    id, school_id, academic_year, parent_classroom_id,
                    workshop_id, workshop_group_set_id, group_number,
                    name, shift, is_active, grade_level_key, status, locked_at,
                    created_at, updated_at
                )
                SELECT
                    id, school_id, academic_year, parent_classroom_id,
                    workshop_id, workshop_group_set_id, group_number,
                    name, shift, is_active, grade_level_key, status, locked_at,
                    created_at, updated_at
                FROM classrooms
            ");

            Schema::drop('classrooms');
            Schema::rename('classrooms__tmp', 'classrooms');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('parent_classroom_id')
                ->references('id')
                ->on('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('workshop_id')
                ->references('id')
                ->on('workshops')
                ->nullOnDelete();
            $table->foreign('workshop_group_set_id')
                ->references('id')
                ->on('workshop_group_sets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('DROP INDEX IF EXISTS uniq_class_school_year_shift_set');

            Schema::create('classrooms__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->index();
                $table->unsignedSmallInteger('academic_year')->nullable();
                $table->foreignId('parent_classroom_id')->nullable()->index();
                $table->foreignId('workshop_id')->nullable()->index();
                $table->foreignId('workshop_group_set_id')->nullable()->index();
                $table->unsignedInteger('group_number')->nullable();
                $table->string('name', 150);
                $table->enum('shift', ['morning','afternoon','evening']);
                $table->boolean('is_active')->default(true);
                $table->string('grade_level_key', 191)->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamp('locked_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['school_id', 'academic_year', 'shift', 'grade_level_key'],
                    'uniq_class_school_year_shift_set'
                );

                $table->index(['school_id', 'academic_year'], 'idx_class_school_year');
                $table->index(['shift', 'is_active'], 'idx_class_shift_active');
                $table->index(['workshop_group_set_id', 'group_number'], 'idx_classroom_group_set_number');
            });

            DB::statement("
                INSERT INTO classrooms__tmp (
                    id, school_id, academic_year, parent_classroom_id,
                    workshop_id, workshop_group_set_id, group_number,
                    name, shift, is_active, grade_level_key, status, locked_at,
                    created_at, updated_at
                )
                SELECT
                    id, school_id, academic_year, parent_classroom_id,
                    workshop_id, workshop_group_set_id, group_number,
                    name, shift, is_active, grade_level_key, status, locked_at,
                    created_at, updated_at
                FROM classrooms
            ");

            Schema::drop('classrooms');
            Schema::rename('classrooms__tmp', 'classrooms');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['parent_classroom_id']);
            $table->dropForeign(['workshop_id']);
            $table->dropForeign(['workshop_group_set_id']);
        });
    }
};
