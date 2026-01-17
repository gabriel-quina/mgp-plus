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

            Schema::create('school_workshop__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id');
                $table->foreignId('workshop_id');
                $table->timestamps();


                $table->foreign('school_id')
                    ->references('id')
                    ->on('schools')
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO school_workshop__tmp (
                    id, school_id, workshop_id, created_at, updated_at
                )
                SELECT
                    id, school_id, workshop_id, created_at, updated_at
                FROM school_workshop
            ");

            Schema::drop('school_workshop');
            Schema::rename('school_workshop__tmp', 'school_workshop');

            DB::statement("
                CREATE INDEX school_workshop_school_id_index
                ON school_workshop (school_id)
            ");
            DB::statement("
                CREATE INDEX school_workshop_workshop_id_index
                ON school_workshop (workshop_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX school_workshop_school_id_workshop_id_unique
                ON school_workshop (school_id, workshop_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('school_workshop', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')
                ->on('schools')
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

            Schema::create('school_workshop__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id');
                $table->foreignId('workshop_id');
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO school_workshop__tmp (
                    id, school_id, workshop_id, created_at, updated_at
                )
                SELECT
                    id, school_id, workshop_id, created_at, updated_at
                FROM school_workshop
            ");

            Schema::drop('school_workshop');
            Schema::rename('school_workshop__tmp', 'school_workshop');

            DB::statement("
                CREATE INDEX school_workshop_school_id_index
                ON school_workshop (school_id)
            ");
            DB::statement("
                CREATE INDEX school_workshop_workshop_id_index
                ON school_workshop (workshop_id)
            ");
            DB::statement("
                CREATE UNIQUE INDEX school_workshop_school_id_workshop_id_unique
                ON school_workshop (school_id, workshop_id)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('school_workshop', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['workshop_id']);
        });
    }
};
