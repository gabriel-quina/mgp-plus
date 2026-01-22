<?php

namespace App\Http\Controllers\Company\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\Schools\Workshops\StoreSchoolWorkshopRequest;
use App\Http\Requests\Company\Schools\Workshops\UpdateSchoolWorkshopRequest;
use App\Models\School;
use App\Models\SchoolWorkshop;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolWorkshopController extends Controller
{
    public function index(School $school): View
    {
        $activeToday = $school->schoolWorkshops()
            ->activeAt()
            ->with('workshop')
            ->orderBy('starts_at')
            ->get();

        $history = $school->schoolWorkshops()
            ->with('workshop')
            ->orderByDesc('starts_at')
            ->get();

        $workshops = Workshop::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('company.schools.workshops', [
            'school' => $school,
            'activeToday' => $activeToday,
            'history' => $history,
            'workshops' => $workshops,
            'statuses' => [
                SchoolWorkshop::STATUS_ACTIVE,
                SchoolWorkshop::STATUS_INACTIVE,
                SchoolWorkshop::STATUS_EXPIRED,
            ],
        ]);
    }

    public function store(StoreSchoolWorkshopRequest $request, School $school): RedirectResponse
    {
        $school->schoolWorkshops()->create($request->validated());

        return redirect()
            ->route('admin.schools.workshops.index', $school)
            ->with('success', 'Oficina vinculada à escola.');
    }

    public function update(UpdateSchoolWorkshopRequest $request, School $school, SchoolWorkshop $schoolWorkshop): RedirectResponse
    {
        // Proteção extra caso scoped bindings não estejam 100%
        abort_unless($schoolWorkshop->school_id === $school->id, 404);

        $schoolWorkshop->update($request->validated());

        return redirect()
            ->route('admin.schools.workshops.index', $school)
            ->with('success', 'Vínculo atualizado.');
    }

    public function destroy(School $school, SchoolWorkshop $schoolWorkshop): RedirectResponse
    {
        abort_unless($schoolWorkshop->school_id === $school->id, 404);

        $schoolWorkshop->delete();

        return redirect()
            ->route('admin.schools.workshops.index', $school)
            ->with('success', 'Vínculo removido.');
    }
}

