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

            Schema::create('assessments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('due_at')->nullable();
                $table->string('scale_type', 20)->default('points');
                $table->decimal('max_points', 5, 2)->default(100.00);
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
                INSERT INTO assessments__tmp (
                    id, classroom_id, workshop_id, title, description,
                    due_at, scale_type, max_points, created_at, updated_at
                )
                SELECT
                    id, classroom_id, workshop_id, title, description,
                    due_at, scale_type, max_points, created_at, updated_at
                FROM assessments
            ");

            Schema::drop('assessments');
            Schema::rename('assessments__tmp', 'assessments');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
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

            Schema::create('assessments__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->index();
                $table->foreignId('workshop_id')->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('due_at')->nullable();
                $table->string('scale_type', 20)->default('points');
                $table->decimal('max_points', 5, 2)->default(100.00);
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO assessments__tmp (
                    id, classroom_id, workshop_id, title, description,
                    due_at, scale_type, max_points, created_at, updated_at
                )
                SELECT
                    id, classroom_id, workshop_id, title, description,
                    due_at, scale_type, max_points, created_at, updated_at
                FROM assessments
            ");

            Schema::drop('assessments');
            Schema::rename('assessments__tmp', 'assessments');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['workshop_id']);
        });
    }
};
