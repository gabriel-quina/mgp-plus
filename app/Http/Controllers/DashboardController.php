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

        $schools = School::query()
            ->whereHas('roleAssignments', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('scope_type', School::class);
            })
            ->orderBy('name')
            ->get();

        return view('schools.dashboard.client', compact('user', 'schools'));
    }
}
