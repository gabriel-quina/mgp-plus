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

            Schema::create('cities__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->index();
                $table->string('name');
                $table->timestamps();

                $table->foreign('state_id')
                    ->references('id')
                    ->on('states')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });

            DB::statement("
                INSERT INTO cities__tmp (
                    id, state_id, name, created_at, updated_at
                )
                SELECT
                    id, state_id, name, created_at, updated_at
                FROM cities
            ");

            Schema::drop('cities');
            Schema::rename('cities__tmp', 'cities');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('cities', function (Blueprint $table) {
            $table->foreign('state_id')
                ->references('id')
                ->on('states')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('cities__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->index();
                $table->string('name');
                $table->timestamps();
            });

            DB::statement("
                INSERT INTO cities__tmp (
                    id, state_id, name, created_at, updated_at
                )
                SELECT
                    id, state_id, name, created_at, updated_at
                FROM cities
            ");

            Schema::drop('cities');
            Schema::rename('cities__tmp', 'cities');

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
        });
    }
};
