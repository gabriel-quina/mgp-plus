<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('workshop_id')
                ->nullable()
                ->after('parent_classroom_id')
                ->constrained('workshops')
                ->nullOnDelete();
            $table->foreignId('workshop_group_set_id')
                ->nullable()
                ->after('workshop_id')
                ->constrained('workshop_group_sets')
                ->nullOnDelete();
            $table->unsignedInteger('group_number')
                ->nullable()
                ->after('workshop_group_set_id');
            $table->string('status', 20)
                ->default('active')
                ->after('grade_level_key');
            $table->timestamp('locked_at')
                ->nullable()
                ->after('status');

            // Index não-único por enquanto (backfill definirá unicidade depois)
            $table->index(['workshop_group_set_id', 'group_number'], 'idx_classroom_group_set_number');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropIndex('idx_classroom_group_set_number');
            $table->dropForeign(['workshop_group_set_id']);
            $table->dropForeign(['workshop_id']);
            $table->dropColumn([
                'workshop_id',
                'workshop_group_set_id',
                'group_number',
                'status',
                'locked_at',
            ]);
        });
    }
};
