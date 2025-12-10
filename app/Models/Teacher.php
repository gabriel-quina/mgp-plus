<?php

namespace App\Models;

use App\Models\Abstract\Person;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Person
{
    use HasFactory;

    protected $table = 'teachers';

    /**
     * Campos graváveis (apenas dados pessoais + status).
     * Demais relações/atributos virão em tabelas próprias.
     */
    protected $fillable = [
        'name',
        'social_name',
        'cpf',
        'email',
        'birthdate',
        'is_active',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_active' => 'boolean',
    ];

    /* ==========================
     |  Relações (próximas etapas)
     |  Mantidas aqui como stubs para facilitar o uso na view teachers.show
     |  (usar string do FQCN evita depender da existência imediata das classes)
     *==========================*/

    /** Vínculos/empregos (our_clt/our_pj/our_temporary/municipal) */
    public function engagements()
    {
        return $this->hasMany('App\Models\TeacherEngagement');
    }

    /** Cidades onde pode atuar quando é nosso funcionário */
    public function cityAccesses()
    {
        return $this->hasMany('App\Models\TeacherCityAccess');
    }

    /** Alocações operacionais em escolas (por ano/turno) */
    public function assignments()
    {
        return $this->hasMany('App\Models\TeachingAssignment');
    }

    /** (Futuro) Aulas lançadas para este professor */
    public function lessons()
    {
        return $this->hasMany('App\Models\Lesson');
    }
}

