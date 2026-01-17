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

            Schema::create('workshop_group_sets__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->unsignedSmallInteger('academic_year');
                $table->string('shift', 50);
                $table->string('grade_levels_signature');
                $table->string('status', 20)->default('active');
                $table->timestamps();

                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO workshop_group_sets__tmp (
                    id, school_id, workshop_id, academic_year, shift,
                    grade_levels_signature, status, created_at, updated_at
                )
                SELECT
                    id, school_id, workshop_id, academic_year, shift,
                    grade_levels_signature, status, created_at, updated_at
                FROM workshop_group_sets
            ");

            Schema::drop('workshop_group_sets');
            Schema::rename('workshop_group_sets__tmp', 'workshop_group_sets');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('workshop_group_sets', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('workshop_id')
                ->references('id')
                ->on('workshops')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('workshop_group_sets__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->unsignedSmallInteger('academic_year');
                $table->string('shift', 50);
                $table->string('grade_levels_signature');
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO workshop_group_sets__tmp (
                    id, school_id, workshop_id, academic_year, shift,
                    grade_levels_signature, status, created_at, updated_at
                )
                SELECT
                    id, school_id, workshop_id, academic_year, shift,
                    grade_levels_signature, status, created_at, updated_at
                FROM workshop_group_sets
            ");

            Schema::drop('workshop_group_sets');
            Schema::rename('workshop_group_sets__tmp', 'workshop_group_sets');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('workshop_group_sets', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['workshop_id']);
        });
    }
};
