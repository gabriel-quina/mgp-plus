<?php
/**
 * app/Http/Middleware/DevContext.php
 *
 * PROPÓSITO (DEV AGORA):
 * - Ler da SESSÃO o "papel ativo" (admin|coordinator) e (quando quiser) a escola ativa.
 * - Construir um App\Support\Context com esses dados e disponibilizá-lo:
 *     1) No container de IoC (app()->instance(Context::class, ...))
 *     2) Para TODAS as views (view()->share('context', ...))
 *
 * USO:
 * - Registrar este middleware no grupo 'web' do Kernel (próximo passo).
 * - Ter um formulário simples no header que envia POST para trocar o papel,
 *   salvando em session('dev.role'). (Esse formulário é 100% DEV.)
 *
 * FUTURO (PROD):
 * - ESTE ARQUIVO pode ser APAGADO. Em produção, você terá outra peça (ex.: AuthContext
 *   ou um Service Provider) que vai montar o mesmo Context lendo de Auth::user()/Spatie.
 * - O restante do app (gates/policies/rotas/blades) continua igual.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Context;

class DevContext
{
    /**
     * Executa antes do controller. Cria e injeta o Context desta request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1) Cria Context a partir da sessão (DEV)
        //    - role padrão: 'admin'
        //    - schoolId padrão: null
        $context = Context::fromSession();

        // 2) Injeta no container para ficar disponível via type-hint (Context $context)
        app()->instance(Context::class, $context);

        // 3) Compartilha com TODAS as views (para usar $context direto no Blade)
        view()->share('context', $context);

        // 4) Siga o fluxo
        return $next($request);
    }
}

