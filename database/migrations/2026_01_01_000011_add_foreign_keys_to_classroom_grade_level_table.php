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

            Schema::create('classroom_grade_level__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->index();
                $table->foreignId('grade_level_id')->index();
                $table->timestamps();

                $table->unique(['classroom_id', 'grade_level_id'], 'uniq_classroom_gradelevel');

                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('grade_level_id')
                    ->references('id')
                    ->on('grade_levels')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO classroom_grade_level__tmp (
                    id, classroom_id, grade_level_id, created_at, updated_at
                )
                SELECT
                    id, classroom_id, grade_level_id, created_at, updated_at
                FROM classroom_grade_level
            ");

            Schema::drop('classroom_grade_level');
            Schema::rename('classroom_grade_level__tmp', 'classroom_grade_level');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classroom_grade_level', function (Blueprint $table) {
            $table->foreign('classroom_id')
                ->references('id')
                ->on('classrooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('grade_level_id')
                ->references('id')
                ->on('grade_levels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('classroom_grade_level__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->index();
                $table->foreignId('grade_level_id')->index();
                $table->timestamps();

                $table->unique(['classroom_id', 'grade_level_id'], 'uniq_classroom_gradelevel');
            });

            DB::statement("
                INSERT INTO classroom_grade_level__tmp (
                    id, classroom_id, grade_level_id, created_at, updated_at
                )
                SELECT
                    id, classroom_id, grade_level_id, created_at, updated_at
                FROM classroom_grade_level
            ");

            Schema::drop('classroom_grade_level');
            Schema::rename('classroom_grade_level__tmp', 'classroom_grade_level');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classroom_grade_level', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['grade_level_id']);
        });
    }
};
