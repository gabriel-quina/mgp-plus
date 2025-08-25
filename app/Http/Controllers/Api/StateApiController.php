<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StateResource;
use App\Models\State;
use Illuminate\Http\Request;

class StateApiController extends Controller
{
    public function index(Request $request)
    {
        $q = State::query()->orderBy('name');
        return StateResource::collection(
            $q->paginate($request->integer('per_page', 27))
        );
    }

    public function show(State $state)
    {
        return new StateResource($state);
    }
}

