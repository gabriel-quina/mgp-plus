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

            Schema::create('workshop_group_set_grade_level__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workshop_group_set_id');
                $table->foreignId('grade_level_id');
                $table->timestamps();


                $table->foreign('workshop_group_set_id')
                    ->references('id')
                    ->on('workshop_group_sets')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('grade_level_id')
                    ->references('id')
                    ->on('grade_levels')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO workshop_group_set_grade_level__tmp (
                    id, workshop_group_set_id, grade_level_id, created_at, updated_at
                )
                SELECT
                    id, workshop_group_set_id, grade_level_id, created_at, updated_at
                FROM workshop_group_set_grade_level
            ");

            Schema::drop('workshop_group_set_grade_level');
            Schema::rename('workshop_group_set_grade_level__tmp', 'workshop_group_set_grade_level');

            DB::statement("
                CREATE INDEX workshop_group_set_grade_level_workshop_group_set_id_index
                ON workshop_group_set_grade_level (workshop_group_set_id)
            ");
            DB::statement("
                CREATE INDEX workshop_group_set_grade_level_grade_level_id_index
                ON workshop_group_set_grade_level (grade_level_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX uniq_wgs_grade_level
                ON workshop_group_set_grade_level (workshop_group_set_id, grade_level_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('workshop_group_set_grade_level', function (Blueprint $table) {
            $table->foreign('workshop_group_set_id')
                ->references('id')
                ->on('workshop_group_sets')
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

            Schema::create('workshop_group_set_grade_level__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workshop_group_set_id');
                $table->foreignId('grade_level_id');
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO workshop_group_set_grade_level__tmp (
                    id, workshop_group_set_id, grade_level_id, created_at, updated_at
                )
                SELECT
                    id, workshop_group_set_id, grade_level_id, created_at, updated_at
                FROM workshop_group_set_grade_level
            ");

            Schema::drop('workshop_group_set_grade_level');
            Schema::rename('workshop_group_set_grade_level__tmp', 'workshop_group_set_grade_level');

            DB::statement("
                CREATE INDEX workshop_group_set_grade_level_workshop_group_set_id_index
                ON workshop_group_set_grade_level (workshop_group_set_id)
            ");
            DB::statement("
                CREATE INDEX workshop_group_set_grade_level_grade_level_id_index
                ON workshop_group_set_grade_level (grade_level_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX uniq_wgs_grade_level
                ON workshop_group_set_grade_level (workshop_group_set_id, grade_level_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('workshop_group_set_grade_level', function (Blueprint $table) {
            $table->dropForeign(['workshop_group_set_id']);
            $table->dropForeign(['grade_level_id']);
        });
    }
};
