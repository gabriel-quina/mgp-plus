<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        $hasNew = Schema::hasTable('student_enrollments');
        $hasOld = Schema::hasTable('student_enrollments_old');

        if (!$hasNew && !$hasOld) {
            DB::statement('PRAGMA foreign_keys = ON');
            throw new RuntimeException('Nenhuma tabela student_enrollments (nem _old) foi encontrada.');
        }

        // Caso comum depois da migration que falhou: só existe _old
        // Caso normal: existe student_enrollments (ainda não renomeada)
        $sourceTable = $hasOld ? 'student_enrollments_old' : 'student_enrollments';

        // Se as duas existirem, valida qual deve prevalecer (segurança)
        if ($hasNew && $hasOld) {
            $newCount = (int) (DB::selectOne('SELECT COUNT(*) AS c FROM student_enrollments')->c ?? 0);
            $oldCount = (int) (DB::selectOne('SELECT COUNT(*) AS c FROM student_enrollments_old')->c ?? 0);

            // Se a nova estiver vazia e a old tiver dados, assumimos que a nova é resíduo de tentativa anterior
            if ($newCount === 0 && $oldCount > 0) {
                Schema::drop('student_enrollments');
                $hasNew = false;
                $sourceTable = 'student_enrollments_old';
            } else {
                DB::statement('PRAGMA foreign_keys = ON');
                throw new RuntimeException(
                    "Encontradas student_enrollments e student_enrollments_old com dados. " .
                    "Resolva manualmente (backup do sqlite) antes de continuar."
                );
            }
        }

        // Se a fonte for a tabela "normal", renomeia para _old para padronizar o fluxo
        if ($sourceTable === 'student_enrollments') {
            Schema::rename('student_enrollments', 'student_enrollments_old');
            $sourceTable = 'student_enrollments_old';
        }

        // Drop índices antigos (nomes colidem ao recriar)
        DB::statement('DROP INDEX IF EXISTS idx_se_student_status');
        DB::statement('DROP INDEX IF EXISTS idx_se_school_year_shift_status');
        DB::statement('DROP INDEX IF EXISTS idx_se_origin_school');

        // Cria a nova tabela com o enum expandido
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('grade_level_id')
                ->constrained('grade_levels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('academic_year');

            $table->enum('shift', ['morning', 'afternoon', 'evening'])
                ->default('morning');

            $table->enum('transfer_scope', ['first', 'internal', 'external'])
                ->default('first');

            $table->foreignId('origin_school_id')
                ->nullable()
                ->constrained('schools')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            // STATUS EXPANDIDO (agora aceita pre_enrolled e enrolled)
            $table->enum('status', [
                'pre_enrolled',
                'enrolled',
                'active',
                'completed',
                'failed',
                'transferred',
                'dropped',
                'suspended',
            ])->default('active'); // mantenho 'active' para não mudar comportamento existente “sem querer”

            $table->timestamps();

            $table->index(['student_id', 'status'], 'idx_se_student_status');
            $table->index(['school_id', 'academic_year', 'shift', 'status'], 'idx_se_school_year_shift_status');
            $table->index('origin_school_id', 'idx_se_origin_school');
        });

        // Copia os dados da tabela antiga para a nova
        DB::statement("
            INSERT INTO student_enrollments (
                id, student_id, school_id, grade_level_id,
                academic_year, shift, transfer_scope, origin_school_id,
                started_at, ended_at, status,
                created_at, updated_at
            )
            SELECT
                id, student_id, school_id, grade_level_id,
                academic_year, shift, transfer_scope, origin_school_id,
                started_at, ended_at, status,
                created_at, updated_at
            FROM {$sourceTable}
        ");

        // Remove a tabela antiga
        Schema::drop($sourceTable);

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $count = (int) (DB::selectOne("
            SELECT COUNT(*) AS c
            FROM student_enrollments
            WHERE status IN ('pre_enrolled','enrolled')
        ")->c ?? 0);

        if ($count > 0) {
            throw new RuntimeException("Rollback bloqueado: existem {$count} matrículas com pre_enrolled/enrolled.");
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::rename('student_enrollments', 'student_enrollments_old');

        DB::statement('DROP INDEX IF EXISTS idx_se_student_status');
        DB::statement('DROP INDEX IF EXISTS idx_se_school_year_shift_status');
        DB::statement('DROP INDEX IF EXISTS idx_se_origin_school');

        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained('grade_levels')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedSmallInteger('academic_year');

            $table->enum('shift', ['morning', 'afternoon', 'evening'])->default('morning');
            $table->enum('transfer_scope', ['first', 'internal', 'external'])->default('first');

            $table->foreignId('origin_school_id')->nullable()->constrained('schools')->nullOnDelete()->cascadeOnUpdate();

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            // ENUM ANTIGO
            $table->enum('status', [
                'active',
                'completed',
                'failed',
                'transferred',
                'dropped',
                'suspended',
            ])->default('active');

            $table->timestamps();

            $table->index(['student_id', 'status'], 'idx_se_student_status');
            $table->index(['school_id', 'academic_year', 'shift', 'status'], 'idx_se_school_year_shift_status');
            $table->index('origin_school_id', 'idx_se_origin_school');
        });

        DB::statement("
            INSERT INTO student_enrollments (
                id, student_id, school_id, grade_level_id,
                academic_year, shift, transfer_scope, origin_school_id,
                started_at, ended_at, status,
                created_at, updated_at
            )
            SELECT
                id, student_id, school_id, grade_level_id,
                academic_year, shift, transfer_scope, origin_school_id,
                started_at, ended_at, status,
                created_at, updated_at
            FROM student_enrollments_old
        ");

        Schema::drop('student_enrollments_old');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};

