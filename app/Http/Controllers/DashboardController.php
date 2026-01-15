<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1) Master
        if ($user->is_master) {
            $schools = School::query()
                ->orderBy('name')
                ->get();

            return view('master', compact('user', 'schools'));
        }

        // 2) Empresa (somente por roles globais reais)
        if ($user->hasRole('company_coordinator')
            || $user->hasRole('company_consultant')) {
            return view('dashboard.company', compact('user'));
        }

        // 3) Cliente (escolas acessíveis)
        $schools = School::query()
            ->whereHas('roleAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('scope_type', School::class);
            })
            ->orderBy('name')
            ->get();

        return view('dashboard.client', compact('user', 'schools'));
    }
}
