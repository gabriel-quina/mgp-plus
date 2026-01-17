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

            Schema::create('classroom_workshop__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->unsignedSmallInteger('max_students')->nullable();
                $table->timestamps();

                $table->unique(['classroom_id', 'workshop_id']);
                $table->index('workshop_id');

                $table->foreign('classroom_id')
                    ->references('id')
                    ->on('classrooms')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->foreign('workshop_id')
                    ->references('id')
                    ->on('workshops')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO classroom_workshop__tmp (
                    id, classroom_id, workshop_id, max_students,
                    created_at, updated_at
                )
                SELECT
                    id, classroom_id, workshop_id, max_students,
                    created_at, updated_at
                FROM classroom_workshop
            ");

            Schema::drop('classroom_workshop');
            Schema::rename('classroom_workshop__tmp', 'classroom_workshop');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classroom_workshop', function (Blueprint $table) {
            $table->foreign('classroom_id')
                ->references('id')
                ->on('classrooms')
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

            Schema::create('classroom_workshop__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->unsignedSmallInteger('max_students')->nullable();
                $table->timestamps();

                $table->unique(['classroom_id', 'workshop_id']);
                $table->index('workshop_id');
            });

            DB::statement("
                INSERT INTO classroom_workshop__tmp (
                    id, classroom_id, workshop_id, max_students,
                    created_at, updated_at
                )
                SELECT
                    id, classroom_id, workshop_id, max_students,
                    created_at, updated_at
                FROM classroom_workshop
            ");

            Schema::drop('classroom_workshop');
            Schema::rename('classroom_workshop__tmp', 'classroom_workshop');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('classroom_workshop', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['workshop_id']);
        });
    }
};
