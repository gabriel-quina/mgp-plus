<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Models\School;
use App\Models\Workshop;
use Illuminate\Http\Request;

class SchoolWorkshopController extends Controller
{
    // Tela para marcar/desmarcar oficinas oferecidas por uma escola
    public function edit(int $schoolId)
    {
        $school    = School::with('city.state')->findOrFail($schoolId);
        $workshops = Workshop::where('is_active', true)->orderBy('name')->get();

        $selected = $school->workshops()->pluck('workshops.id')->all();

        return view('schools.workshops', compact('school', 'workshops', 'selected'));
    }

    // Salva as oficinas da escola (sync no pivot)
    public function update(Request $request, int $schoolId)
    {
        $school = School::findOrFail($schoolId);
        $ids = $request->input('workshops', []); // array de IDs

        $school->workshops()->sync($ids);

        return redirect()->route('schools.show', $school)
            ->with('success', 'Oficinas da escola atualizadas.');
    }
}
