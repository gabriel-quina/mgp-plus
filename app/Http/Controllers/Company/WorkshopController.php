<?php

namespace App\Http\Controllers;

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
                $w->orwhere('name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(15);

        return view('workshops.index', compact('workshops', 'q'));
    }

    public function create()
    {
        return view('workshops.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateWorkshop($request);
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        Workshop::create($data);

        return redirect()
            ->route('workshops.index')
            ->with('success', 'Oficina criada com sucesso.');
    }

    public function edit(Workshop $workshop)
    {
        return view('workshops.edit', compact('workshop'));
    }

    public function update(Request $request, Workshop $workshop)
    {
        $data = $this->validateWorkshop($request, $workshop->id);
        $data['is_active'] = (bool)($data['is_active'] ?? false);

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
                ],
                'description' => ['nullable', 'string', 'max:5000'],
                'is_active'   => ['sometimes', 'boolean'],
            ],
            [
                'name.required' => 'Informe o nome da oficina.',
            ],
            [
                'name' => 'Nome da oficina',
            ]
        );
    }
}


