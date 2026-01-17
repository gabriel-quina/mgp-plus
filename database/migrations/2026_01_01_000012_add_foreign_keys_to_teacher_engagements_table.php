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

            Schema::create('teacher_engagements__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id');
                $table->string('engagement_type', 32);
                $table->unsignedTinyInteger('hours_per_week');
                $table->string('status', 24)->default('active');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->foreignId('city_id')->nullable();
                $table->string('notes', 500)->nullable();
                $table->timestamps();


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
                INSERT INTO teacher_engagements__tmp (
                    id, teacher_id, engagement_type, hours_per_week, status,
                    start_date, end_date, city_id, notes,
                    created_at, updated_at
                )
                SELECT
                    id, teacher_id, engagement_type, hours_per_week, status,
                    start_date, end_date, city_id, notes,
                    created_at, updated_at
                FROM teacher_engagements
            ");

            Schema::drop('teacher_engagements');
            Schema::rename('teacher_engagements__tmp', 'teacher_engagements');

            DB::statement("
                CREATE INDEX teacher_engagements_teacher_id_index
                ON teacher_engagements (teacher_id)
            ");
            DB::statement("
                CREATE INDEX teacher_engagements_city_id_index
                ON teacher_engagements (city_id)
            ");
            DB::statement("
                CREATE INDEX teacher_engagements_teacher_id_status_index
                ON teacher_engagements (teacher_id, status)
            ");
            DB::statement("
                CREATE INDEX teacher_engagements_engagement_type_index
                ON teacher_engagements (engagement_type)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('teacher_engagements', function (Blueprint $table) {
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

            Schema::create('teacher_engagements__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id');
                $table->string('engagement_type', 32);
                $table->unsignedTinyInteger('hours_per_week');
                $table->string('status', 24)->default('active');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->foreignId('city_id')->nullable();
                $table->string('notes', 500)->nullable();
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO teacher_engagements__tmp (
                    id, teacher_id, engagement_type, hours_per_week, status,
                    start_date, end_date, city_id, notes,
                    created_at, updated_at
                )
                SELECT
                    id, teacher_id, engagement_type, hours_per_week, status,
                    start_date, end_date, city_id, notes,
                    created_at, updated_at
                FROM teacher_engagements
            ");

            Schema::drop('teacher_engagements');
            Schema::rename('teacher_engagements__tmp', 'teacher_engagements');

            DB::statement("
                CREATE INDEX teacher_engagements_teacher_id_index
                ON teacher_engagements (teacher_id)
            ");
            DB::statement("
                CREATE INDEX teacher_engagements_city_id_index
                ON teacher_engagements (city_id)
            ");
            DB::statement("
                CREATE INDEX teacher_engagements_teacher_id_status_index
                ON teacher_engagements (teacher_id, status)
            ");
            DB::statement("
                CREATE INDEX teacher_engagements_engagement_type_index
                ON teacher_engagements (engagement_type)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('teacher_engagements', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['city_id']);
        });
    }
};
