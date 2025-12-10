<?php
/**
 * app/Support/Context.php
 *
 * OBJETIVO (DEV/AGORA):
 * - Representar o "contexto" da request: papel ativo (admin|coordinator)
 *   e, no futuro, a escola ativa.
 * - Hoje a origem desse contexto é a SESSÃO (porque não queremos login).
 * - Outros componentes (Gates, Middlewares, Controllers, Views) vão LER daqui.
 *
 * FUTURO (PROD):
 * - Este mesmo objeto pode continuar existindo, mas a origem dos dados muda:
 *   em vez de session('dev.*'), vamos ler de Auth::user() e/ou do pacote de
 *   permissões (ex.: Spatie). Assim, *não* precisamos refatorar o resto do app.
 */

namespace App\Support;

class Context
{
    /* ---------------------------------------------------------------------
     |  PAPÉIS SUPORTADOS (mantemos aqui para evitar "string solta" no app)
     |--------------------------------------------------------------------- */
    public const ROLE_ADMIN       = 'admin';
    public const ROLE_COORDINATOR = 'coordinator';

    /** Papel ativo desta request. Ex.: 'admin' ou 'coordinator'. */
    public string $role;

    /**
     * (Opcional) Escola ativa para escopo de dados.
     * Não vamos usar agora, mas já deixamos o campo para quando habilitar.
     */
    public ?int $schoolId = null;

    /* ---------------------------------------------------------------------
     |  CONSTRUÇÃO
     |--------------------------------------------------------------------- */

    /**
     * Construtor "agnóstico". Em DEV, vamos instanciar a partir da sessão.
     * Em PROD, podemos instanciar a partir do usuário autenticado.
     */
    public function __construct(string $role = self::ROLE_ADMIN, ?int $schoolId = null)
    {
        $this->role     = $this->normalizeRole($role);
        $this->schoolId = $schoolId;
    }

    /**
     * FÁBRICA (DEV): cria o Context lendo da sessão.
     * - Role: session('dev.role'), default = 'admin'
     * - School: session('dev.school_id'), default = null
     *
     * Em produção, NÃO use esta fábrica; crie outra (ex.: fromUser()).
     */
    public static function fromSession(): self
    {
        $role     = session('dev.role', self::ROLE_ADMIN);
        $schoolId = session()->has('dev.school_id') ? (int) session('dev.school_id') : null;

        return new self($role, $schoolId);
    }

    /**
     * (Opcional) Persistir de volta na sessão quando trocar algo via UI (DEV).
     * Útil para o seletor de papel/escola no cabeçalho.
     */
    public function saveToSession(): void
    {
        session([
            'dev.role'      => $this->role,
            'dev.school_id' => $this->schoolId,
        ]);
    }

    /* ---------------------------------------------------------------------
     |  HELPERS DE PAPEL
     |--------------------------------------------------------------------- */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCoordinator(): bool
    {
        return $this->role === self::ROLE_COORDINATOR;
    }

    /**
     * Troca de papel (DEV). Em PROD, isso virá do usuário logado.
     * Dica: depois de chamar setRole(), chame saveToSession() se quiser persistir.
     */
    public function setRole(string $role): void
    {
        $this->role = $this->normalizeRole($role);
    }

    /**
     * Define/limpa escola ativa (não usado agora, mas já preparado).
     */
    public function setSchoolId(?int $schoolId): void
    {
        $this->schoolId = $schoolId;
    }

    /* ---------------------------------------------------------------------
     |  PRIVADOS
     |--------------------------------------------------------------------- */

    /**
     * Aceita apenas valores suportados; qualquer outro vira 'admin' por segurança.
     * Em PROD, essa normalização pode ser mais rígida (lançar exceção).
     */
    private function normalizeRole(string $role): string
    {
        $r = strtolower(trim($role));
        return in_array($r, [self::ROLE_ADMIN, self::ROLE_COORDINATOR], true)
            ? $r
            : self::ROLE_ADMIN;
    }
}

