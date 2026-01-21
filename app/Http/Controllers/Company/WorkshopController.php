<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkshopController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $workshops = Workshop::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('company.workshops.index', compact('workshops', 'q'));
    }

    public function create()
    {
        return view('company.workshops.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateWorkshop($request);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        Workshop::create($data);

        return redirect()
            ->route('workshops.index')
            ->with('success', 'Oficina criada com sucesso.');
    }

    public function edit(Workshop $workshop)
    {
        return view('company.workshops.edit', compact('workshop'));
    }

    public function update(Request $request, Workshop $workshop)
    {
        $data = $this->validateWorkshop($request, $workshop->id);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $workshop->update($data);

        return redirect()
            ->route('workshops.index')
            ->with('success', 'Oficina atualizada com sucesso.');
    }

    // ------------------------------------------------------------------------------------

    private function validateWorkshop(Request $request, $id = null): array
    {
        return $request->validate(
            [
                'name'        => [
                    'required', 'string', 'max:150',
                    Rule::unique('workshops', 'name')->ignore($id),

