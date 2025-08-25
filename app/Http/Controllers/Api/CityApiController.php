<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CityApiController extends Controller
{
    public function index(Request $request)
    {
        $q = City::query()->with('state:id,name,uf');

        if ($s = $request->query('search')) {
            $q->where('name', 'like', "%{$s}%");
        }

        return CityResource::collection(
            $q->orderBy('name')->paginate($request->integer('per_page', 15))
        );
    }

    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated())->load('state:id,name,uf');

        return (new CityResource($city))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(City $city)
    {
        return new CityResource($city->load('state:id,name,uf'));
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());
        $city->load('state:id,name,uf');

        return new CityResource($city);
    }

    public function destroy(City $city)
    {
        $city->delete();
        return response()->noContent();
    }
}

