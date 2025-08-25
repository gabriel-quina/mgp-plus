<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SchoolApiController extends Controller
{
    public function index(Request $request)
    {
        $q = School::query()->with('city.state:id,name,uf');

        if ($s = $request->query('search')) {
            $q->where('name', 'like', "%{$s}%")
              ->orWhere('cep', 'like', "%{$s}%");
        }

        return SchoolResource::collection(
            $q->orderBy('name')->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreSchoolRequest $request)
    {
        $school = School::create($request->validated())->load('city.state:id,name,uf');

        return (new SchoolResource($school))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(School $school)
    {
        return new SchoolResource($school->load('city.state:id,name,uf'));
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $school->update($request->validated());
        $school->load('city.state:id,name,uf');

        return new SchoolResource($school);
    }

    public function destroy(School $school)
    {
        $school->delete();
        return response()->noContent();
    }
}

