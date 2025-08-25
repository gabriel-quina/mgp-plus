<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\School;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('school')->orderBy('name')->get();

        return view('students.index', compact('students'));
    }

    public function create()
    {
        $schools = School::orderBy('name')->get();

        return view('students.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'cpf'        => ['nullable', 'string', 'max:20', 'unique:students,cpf'],
            'email'      => ['nullable', 'email', 'max:255', 'unique:students,email'],
            'birthdate'  => ['nullable', 'date'],
            'school_id'  => ['required', 'exists:schools,id'],
        ]);

        Student::create($data);

        return redirect()
            ->route('students.index')
            ->with('success', 'Aluno criado com sucesso!');
    }
}

