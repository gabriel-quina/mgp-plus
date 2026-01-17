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

            Schema::create('lessons__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id');
                $table->foreignId('workshop_id');
                $table->date('taught_at');
                $table->string('topic')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_locked')->default(false);
                $table->timestamps();


                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO lessons__tmp (
                    id, classroom_id, workshop_id, taught_at, topic, notes,
                    is_locked, created_at, updated_at
                )
                SELECT
                    id, classroom_id, workshop_id, taught_at, topic, notes,
                    is_locked, created_at, updated_at
                FROM lessons
            ");

            Schema::drop('lessons');
            Schema::rename('lessons__tmp', 'lessons');

            DB::statement("
                CREATE INDEX lessons_classroom_id_index
                ON lessons (classroom_id)
            ");
            DB::statement("
                CREATE INDEX lessons_workshop_id_index
                ON lessons (workshop_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX lessons_unique_slot
                ON lessons (classroom_id, workshop_id, taught_at, starts_at)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreign('classroom_id')
                ->references('id')
                ->on('classrooms')
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

            Schema::create('lessons__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id');
                $table->foreignId('workshop_id');
                $table->date('taught_at');
                $table->string('topic')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_locked')->default(false);
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO lessons__tmp (
                    id, classroom_id, workshop_id, taught_at, topic, notes,
                    is_locked, created_at, updated_at
                )
                SELECT
                    id, classroom_id, workshop_id, taught_at, topic, notes,
                    is_locked, created_at, updated_at
                FROM lessons
            ");

            Schema::drop('lessons');
            Schema::rename('lessons__tmp', 'lessons');

            DB::statement("
                CREATE INDEX lessons_classroom_id_index
                ON lessons (classroom_id)
            ");
            DB::statement("
                CREATE INDEX lessons_workshop_id_index
                ON lessons (workshop_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX lessons_unique_slot
                ON lessons (classroom_id, workshop_id, taught_at, starts_at)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['workshop_id']);
        });
    }
};
