<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user()->fresh(['userScope']);

        // Evita loop: se url.intended = /login, remove
        $intended = $request->session()->get('url.intended');
        if (is_string($intended)) {
            $path = parse_url($intended, PHP_URL_PATH) ?? '';
            if ($path === '/login') {
                $request->session()->forget('url.intended');
            }
        }

        // MASTER: sempre company/dashboard (master é transversal via is_master)
        if ((bool) $user->is_master) {
            $request->session()->put('acting_scope', 'company');
            $request->session()->forget('acting_school_id');

            return redirect()->route('admin.dashboard');
        }

        // COMPANY: sempre /admin/dashboard
        if ($user->isCompany()) {
            $request->session()->put('acting_scope', 'company');
            $request->session()->forget('acting_school_id');

            return redirect()->route('admin.dashboard');
        }

        // SCHOOL: se tiver 1 escola, fixa e entra; se >1, precisa seletor (fallback por enquanto)
        if ($user->isSchool()) {
            // 1) tenta usar actingSchool (pode vir da sessão, ou resolver se tiver 1 escola)
            $acting = $user->actingSchool();

            // 2) se ainda não tem, tenta resolver se tiver exatamente 1 escola acessível
            if (! $acting) {
                $schools = $user->accessibleSchools();
                if ($schools->count() === 1) {
                    $school = $schools->first();
                    $schoolId = (int) ($school?->id ?? 0);

                    if ($schoolId > 0) {
                        $request->session()->put('acting_scope', 'school');
                        $request->session()->put('acting_school_id', $schoolId);

                        $acting = $user->actingSchool();
                    }
                }
            }

            if ($acting) {
                return redirect()->route('schools.dashboard', ['school' => $acting->id]);
            }

            /**
             * Chegou aqui:
             * - usuário é school, mas tem 0 escolas ou >1 escolas.
             *
             * Você ainda não definiu rota de seletor.
             * Então eu não vou inventar.
             *
             * Fallback seguro:
             * - desloga e volta pro login com erro claro
             * (melhor do que mandar pra /admin/dashboard e misturar escopos)
             */
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Seu usuário é do escopo escola, mas não foi possível determinar a escola. Verifique acessos (0 ou múltiplas escolas).']);
        }

        // Usuário sem escopo válido em user_scopes
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['login' => 'Seu usuário não possui escopo (company/school) definido.']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

