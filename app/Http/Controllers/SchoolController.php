<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with('city.state')
            ->orderBy('name')
            ->get();

        return view('schools.index', compact('schools'));
    }

    public function create()
    {
        $cities = City::with('state')->orderBy('name')->get();
        return view('schools.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'city_id' => ['required', 'exists:cities,id'],
            'cep'     => ['nullable', 'string', 'size:8'],
        ]);

        School::create($data);

        return redirect()
            ->route('schools.index')
            ->with('success', 'Escola criada com sucesso!');
    }
}

