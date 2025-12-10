<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'administrative_dependency')) {
                $table->string('administrative_dependency')
                    ->default('municipal')
                    ->after('city_id');
                // municipal | state | private | federal (se quiser depois)
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'administrative_dependency')) {
                $table->dropColumn('administrative_dependency');
            }
        });
    }
};
