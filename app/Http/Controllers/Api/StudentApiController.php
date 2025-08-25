<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StudentApiController extends Controller
{
    public function index(Request $request)
    {
        $q = Student::query()->with('school:id,name');

        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('name','like',"%{$s}%")
                  ->orWhere('cpf','like',"%{$s}%")
                  ->orWhere('email','like',"%{$s}%");
            });
        }

        return StudentResource::collection(
            $q->orderBy('name')->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated())->load('school:id,name');

        return (new StudentResource($student))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Student $student)
    {
        return new StudentResource($student->load('school:id,name'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student->update($request->validated());
        $student->load('school:id,name');

        return new StudentResource($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->noContent();
    }
}

