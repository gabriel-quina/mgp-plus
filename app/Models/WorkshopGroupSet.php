<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopGroupSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'workshop_id',
        'academic_year',
        'shift',
        'grade_levels_signature',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'workshop_group_set_grade_level')
            ->withTimestamps();
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'workshop_group_set_id');
    }
}
