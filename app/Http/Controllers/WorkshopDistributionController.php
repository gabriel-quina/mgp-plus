<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Workshop;
use App\Services\WorkshopDistributionService;
use Illuminate\Http\Request;

class WorkshopDistributionController extends Controller
{
    public function __construct(private WorkshopDistributionService $service)
    {
        //
    }

    /**
     * Mostra a PRÉVIA da distribuição por oficina para a Turma PAI.
     */
    public function preview(Classroom $classroom, Workshop $workshop)
    {
        // segurança básica: só turma PAI participa da distribuição
        if ($classroom->parent_classroom_id !== null) {
            abort(404);
        }

        // Dados para a prévia (elegíveis, capacidade, buckets calculados)
        $result = $this->service->preview($classroom, $workshop);

        return view('classrooms.sections.preview', [
            'classroom' => $classroom,
            'workshop' => $workshop,
            'eligible' => $result['eligible'],
            'capacity' => $result['capacity'],
            'buckets' => $result['buckets'],
        ]);
    }

    /**
     * APLICA a distribuição (cria/reutiliza subturmas, limpa auto-alocações e aloca round-robin).
     */
    public function apply(Request $request, Classroom $classroom, Workshop $workshop)
    {
        if ($classroom->parent_classroom_id !== null) {
            abort(404);
        }

        $this->service->apply($classroom, $workshop);

        return redirect()
            ->route('classrooms.workshops.subclasses.index', [$classroom, $workshop])
            ->with('success', "Distribuição aplicada para {$workshop->name}.");
    }

    public function adjustCapacity(Request $request, Classroom $classroom, Workshop $workshop)
    {
        // Só turma PAI
        if ($classroom->parent_classroom_id !== null) {
            abort(404);
        }

        $data = $request->validate([
            'new_capacity' => ['required', 'integer', 'min:1'],
        ]);

        $newCapacity = (int) $data['new_capacity'];

        // garante que a oficina está vinculada à turma PAI
        $pivotRow = $classroom->workshops()
            ->where('workshops.id', $workshop->id)
            ->first()?->pivot;

        if (! $pivotRow) {
            abort(404);
        }

        // atualiza o max_students no pivot classroom_workshop
        $classroom->workshops()->updateExistingPivot($workshop->id, [
            'max_students' => $newCapacity,
        ]);

        return redirect()
            ->route('classrooms.workshops.show', [$classroom, $workshop])
            ->with('success', "Capacidade da oficina atualizada para {$newCapacity} aluno(s). Esta oficina agora usa a turma inteira (sem subturmas).");
    }
}
