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

            Schema::create('lesson_attendances__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id');
                $table->foreignId('student_enrollment_id');
                $table->boolean('present')->default(true);
                $table->string('justification')->nullable();
                $table->timestamps();


                $table->foreign('lesson_id')
                    ->references('id')
                    ->on('lessons')
                    ->cascadeOnDelete();
                $table->foreign('student_enrollment_id')
                    ->references('id')
                    ->on('student_enrollments')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO lesson_attendances__tmp (
                    id, lesson_id, student_enrollment_id, present,
                    justification, created_at, updated_at
                )
                SELECT
                    id, lesson_id, student_enrollment_id, present,
                    justification, created_at, updated_at
                FROM lesson_attendances
            ");

            Schema::drop('lesson_attendances');
            Schema::rename('lesson_attendances__tmp', 'lesson_attendances');

            DB::statement("
                CREATE INDEX lesson_attendances_lesson_id_index
                ON lesson_attendances (lesson_id)
            ");
            DB::statement("
                CREATE INDEX lesson_attendances_student_enrollment_id_index
                ON lesson_attendances (student_enrollment_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX lesson_attendances_lesson_id_student_enrollment_id_unique
                ON lesson_attendances (lesson_id, student_enrollment_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('lesson_attendances', function (Blueprint $table) {
            $table->foreign('lesson_id')
                ->references('id')
                ->on('lessons')
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

            Schema::create('lesson_attendances__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lesson_id');
                $table->foreignId('student_enrollment_id');
                $table->boolean('present')->default(true);
                $table->string('justification')->nullable();
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO lesson_attendances__tmp (
                    id, lesson_id, student_enrollment_id, present,
                    justification, created_at, updated_at
                )
                SELECT
                    id, lesson_id, student_enrollment_id, present,
                    justification, created_at, updated_at
                FROM lesson_attendances
            ");

            Schema::drop('lesson_attendances');
            Schema::rename('lesson_attendances__tmp', 'lesson_attendances');

            DB::statement("
                CREATE INDEX lesson_attendances_lesson_id_index
                ON lesson_attendances (lesson_id)
            ");
            DB::statement("
                CREATE INDEX lesson_attendances_student_enrollment_id_index
                ON lesson_attendances (student_enrollment_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX lesson_attendances_lesson_id_student_enrollment_id_unique
                ON lesson_attendances (lesson_id, student_enrollment_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('lesson_attendances', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropForeign(['student_enrollment_id']);
        });
    }
};
