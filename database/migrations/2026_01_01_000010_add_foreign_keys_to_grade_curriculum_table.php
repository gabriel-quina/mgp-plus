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

            Schema::create('grade_curriculum__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id');
                $table->string('academic_period', 32);
                $table->foreignId('grade_level_id');
                $table->foreignId('workshop_id');
                $table->timestamps();


                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnDelete();
                $table->foreign('grade_level_id')
                    ->references('id')
                    ->on('grade_levels')
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO grade_curriculum__tmp (
                    id, school_id, academic_period, grade_level_id, workshop_id,
                    created_at, updated_at
                )
                SELECT
                    id, school_id, academic_period, grade_level_id, workshop_id,
                    created_at, updated_at
                FROM grade_curriculum
            ");

            Schema::drop('grade_curriculum');
            Schema::rename('grade_curriculum__tmp', 'grade_curriculum');

            DB::statement("
                CREATE INDEX grade_curriculum_school_id_index
                ON grade_curriculum (school_id)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_grade_level_id_index
                ON grade_curriculum (grade_level_id)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_workshop_id_index
                ON grade_curriculum (workshop_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX uniq_grade_curriculum
                ON grade_curriculum (school_id, academic_period, grade_level_id, workshop_id)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_school_id_academic_period_index
                ON grade_curriculum (school_id, academic_period)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_grade_level_id_academic_period_index
                ON grade_curriculum (grade_level_id, academic_period)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_workshop_id_academic_period_index
                ON grade_curriculum (workshop_id, academic_period)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('grade_curriculum', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->cascadeOnDelete();
            $table->foreign('grade_level_id')
                ->references('id')
                ->on('grade_levels')
                ->cascadeOnDelete();
            $table->foreign('workshop_id')
                ->references('id')
                ->on('workshops')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('grade_curriculum__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id');
                $table->string('academic_period', 32);
                $table->foreignId('grade_level_id');
                $table->foreignId('workshop_id');
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO grade_curriculum__tmp (
                    id, school_id, academic_period, grade_level_id, workshop_id,
                    created_at, updated_at
                )
                SELECT
                    id, school_id, academic_period, grade_level_id, workshop_id,
                    created_at, updated_at
                FROM grade_curriculum
            ");

            Schema::drop('grade_curriculum');
            Schema::rename('grade_curriculum__tmp', 'grade_curriculum');

            DB::statement("
                CREATE INDEX grade_curriculum_school_id_index
                ON grade_curriculum (school_id)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_grade_level_id_index
                ON grade_curriculum (grade_level_id)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_workshop_id_index
                ON grade_curriculum (workshop_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX uniq_grade_curriculum
                ON grade_curriculum (school_id, academic_period, grade_level_id, workshop_id)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_school_id_academic_period_index
                ON grade_curriculum (school_id, academic_period)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_grade_level_id_academic_period_index
                ON grade_curriculum (grade_level_id, academic_period)
            ");
            DB::statement("
                CREATE INDEX grade_curriculum_workshop_id_academic_period_index
                ON grade_curriculum (workshop_id, academic_period)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('grade_curriculum', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['grade_level_id']);
            $table->dropForeign(['workshop_id']);
        });
    }
};
