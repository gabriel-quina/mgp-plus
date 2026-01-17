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

            Schema::create('schools__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('city_id');
                $table->string('administrative_dependency')->default('municipal');
                $table->string('name', 150);
                $table->boolean('is_historical')->default(false);
                $table->string('street', 150)->nullable();
                $table->string('number', 20)->nullable();
                $table->string('neighborhood', 120)->nullable();
                $table->string('complement', 120)->nullable();
                $table->string('cep', 9)->nullable();
                $table->timestamps();

                $table->foreign('city_id')
                    ->references('id')
                    ->on('cities')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });

            DB::statement("
                INSERT INTO schools__tmp (
                    id, city_id, administrative_dependency, name, is_historical,
                    street, number, neighborhood, complement, cep,
                    created_at, updated_at
                )
                SELECT
                    id, city_id, administrative_dependency, name, is_historical,
                    street, number, neighborhood, complement, cep,
                    created_at, updated_at
                FROM schools
            ");

            Schema::drop('schools');
            Schema::rename('schools__tmp', 'schools');

            DB::statement("
                CREATE INDEX schools_city_id_index
                ON schools (city_id)
            ");
            DB::statement("
                CREATE INDEX schools_name_index
                ON schools (name)
            ");
            DB::statement("
                CREATE INDEX schools_is_historical_index
                ON schools (is_historical)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->foreign('city_id')
                ->references('id')
                ->on('cities')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('schools__tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('city_id');
                $table->string('administrative_dependency')->default('municipal');
                $table->string('name', 150);
                $table->boolean('is_historical')->default(false);
                $table->string('street', 150)->nullable();
                $table->string('number', 20)->nullable();
                $table->string('neighborhood', 120)->nullable();
                $table->string('complement', 120)->nullable();
                $table->string('cep', 9)->nullable();
                $table->timestamps();

            });

            DB::statement("
                INSERT INTO schools__tmp (
                    id, city_id, administrative_dependency, name, is_historical,
                    street, number, neighborhood, complement, cep,
                    created_at, updated_at
                )
                SELECT
                    id, city_id, administrative_dependency, name, is_historical,
                    street, number, neighborhood, complement, cep,
                    created_at, updated_at
                FROM schools
            ");

            Schema::drop('schools');
            Schema::rename('schools__tmp', 'schools');

            DB::statement("
                CREATE INDEX schools_city_id_index
                ON schools (city_id)
            ");
            DB::statement("
                CREATE INDEX schools_name_index
                ON schools (name)
            ");
            DB::statement("
                CREATE INDEX schools_is_historical_index
                ON schools (is_historical)
            ");

            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
        });
    }
};
