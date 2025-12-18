# 🎓 MGP+ – Módulo de Gestão Pedagógica Plus

**MGP+** é um sistema interno da **Uptake Education** para organizar oficinas pedagógicas em redes públicas: cadastro de cidades, escolas, turmas, professores, coordenações e alunos; criação e distribuição de oficinas; lançamento de presença e desempenho; acompanhamento descentralizado por coordenadores.

---

## 📚 Sobre o projeto

A aplicação é um monolito Laravel focado em operações pedagógicas e administrativas:

- Catálogos de cidades, escolas, professores, coordenadores e alunos.
- Organização de oficinas por região/escola, alocação de professores e formação de turmas.
- Matrículas e distribuição de alunos em oficinas, com acompanhamento de presenças e resultados.
- Interfaces web renderizadas em Blade com navegação por papéis (master/rede e escola).

---

## 🛠️ Tecnologias

- PHP 8.2+
- **Laravel 12**
- Laravel Breeze (Blade)
- Blade + Bootstrap 5 (CDN) para UI; toolchain Vite/Tailwind disponível para assets
- SQLite para prototipagem (suporta outros bancos configurando o `.env`)
- Composer, Artisan, Node.js (Vite)
- Pest para testes

---

## ⚙️ Como rodar localmente

> Requisitos: PHP 8.2+, Composer, Node.js 18+, SQLite 3 (ou outro banco configurado no `.env`).

```bash
# Clonar o repositório
git clone git@github.com:gabriel-quina/mgp-plus.git
cd mgp-plus

# Instalar dependências de back-end e front-end
composer install
npm install

# Configurar variáveis de ambiente
cp .env.example .env
php artisan key:generate

# Banco SQLite para desenvolvimento rápido
mkdir -p database
touch database/database.sqlite
php artisan migrate

# Servidores de aplicação e Vite (dois terminais). Use portas diferentes
# para evitar conflito (o script do Vite está configurado para 8000).
php artisan serve --port=8001
npm run dev -- --port=8000
# ou use o helper em um terminal para mudar apenas o backend:
# ./start-dev.sh 0.0.0.0 8001
```

---

## 🧪 Testes

```bash
php artisan test
```

---

## 🧭 Estrutura de pastas (resumo)

- `routes/web.php` e subpastas em `routes/web/*`: agrupamento de rotas por escopo (master e escola).
- `app/Http/Controllers`: controllers REST e formulários (Requests) para recursos pedagógicos.
- `app/Services`: regras de negócio mais ricas (distribuição de oficinas, alocações, etc.).
- `resources/views`: Blade + Bootstrap 5 para dashboards, cadastros e formulários.
- `database/migrations`: esquema relacional (turmas, oficinas, matrículas, presenças, etc.).

