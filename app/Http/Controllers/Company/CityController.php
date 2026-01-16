<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Http\Requests\CityRequest;
use App\Models\City;
use App\Models\State;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('state')->paginate(10);

        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        $states = State::pluck('name', 'id');

        return view('cities.create', compact('states'));
    }

    public function store(CityRequest $request)
    {
        City::create($request->validated());

        return redirect()
            ->route('cities.index')
            ->with('success', 'Cidade criada com sucesso!');
    }

    public function show(City $city)
    {
        return view('cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        $states = State::pluck('name', 'id');

        return view('cities.edit', compact('city', 'states'));
    }

    public function update(CityRequest $request, City $city)
    {
        $city->update($request->validated());

        return redirect()
            ->route('cities.index')
            ->with('success', 'Cidade atualizada com sucesso!');
    }

    public function destroy(City $city)
    {
        $city->delete();

        return redirect()
            ->route('cities.index')
            ->with('success', 'Cidade excluída com sucesso!');
    }
}

