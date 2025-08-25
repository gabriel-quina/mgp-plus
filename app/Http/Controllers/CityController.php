<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('state')->orderBy('name')->get();
        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        $states = State::orderBy('name')->get();
        return view('cities.create', compact('states'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:120'],
            'state_id' => ['required','exists:states,id'],
        ]);

        City::create($data);

        return redirect()
            ->route('cities.index')
            ->with('success', 'Cidade criada com sucesso!');
    }
}

