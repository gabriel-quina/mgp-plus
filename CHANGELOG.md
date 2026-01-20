# 📓 Changelog

Todas as mudanças notáveis neste projeto serão documentadas aqui.

---

## [Unreleased]

### ✨ Adicionado
- Models e Controllers para `Student`, `School`, `City` e `State`
- Controllers específicos de API: `CityApiController`, `SchoolApiController`, `StateApiController`, `StudentApiController`
- Requests de validação (`Store*` e `Update*`) para entidades principais
- API Resources para serialização de dados
- Migrations refatoradas para `states`, `cities`, `schools` e `students`
- Factories e Seeders (`StateSeeder`, `StudentSeeder`) para popular dados
- Novas views Blade para `students`, `schools` e `cities`
- Arquivo de rotas `api.php` para endpoints REST

### 🔧 Alterado
- `bootstrap/app.php` atualizado
- `DatabaseSeeder.php` adaptado para novos seeders
- `package.json` com ajustes de dependências
- `routes/web.php` reorganizado para novas entidades
- `school_workshop` agora suporta vigência/estado para manter histórico de contratos
- `Classroom` passa a usar `school_workshop_id` como vínculo principal de oficina

### 🗑 Removido
- Estrutura antiga de `Aluno` e `Pessoa`
- Controllers, Models, Migrations e Seeders relacionados a `Aluno`
- Views Blade de `alunos`
