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

            Schema::create('teacher_city_access__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->index();
                $table->foreignId('city_id')->index();
                $table->timestamps();

                $table->unique(['teacher_id', 'city_id']);

                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('teachers')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('city_id')
                    ->references('id')
                    ->on('cities')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });

            DB::statement("
                INSERT INTO teacher_city_access__tmp (
                    id, teacher_id, city_id, created_at, updated_at
                )
                SELECT
                    id, teacher_id, city_id, created_at, updated_at
                FROM teacher_city_access
            ");

            Schema::drop('teacher_city_access');
            Schema::rename('teacher_city_access__tmp', 'teacher_city_access');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('teacher_city_access', function (Blueprint $table) {
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('city_id')
                ->references('id')
                ->on('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('teacher_city_access__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->index();
                $table->foreignId('city_id')->index();
                $table->timestamps();

                $table->unique(['teacher_id', 'city_id']);
            });

            DB::statement("
                INSERT INTO teacher_city_access__tmp (
                    id, teacher_id, city_id, created_at, updated_at
                )
                SELECT
                    id, teacher_id, city_id, created_at, updated_at
                FROM teacher_city_access
            ");

            Schema::drop('teacher_city_access');
            Schema::rename('teacher_city_access__tmp', 'teacher_city_access');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('teacher_city_access', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['city_id']);
        });
    }
};
