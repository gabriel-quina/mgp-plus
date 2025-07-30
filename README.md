# 🎓 MGP+ – Módulo de Gestão Pedagógica Plus

**MGP+** é um sistema interno da **Uptake Education** desenvolvido para gerenciar oficinas pedagógicas aplicadas em escolas públicas. Ele permite organizar e acompanhar atividades como Robótica, Teatro, Inglês, Xadrez, entre outras, realizadas por professores capacitados em diferentes regiões do estado.

---

## 📚 Sobre o projeto

MGP+ (Módulo de Gestão Pedagógica Plus) é uma aplicação web com foco em:

- Cadastro de cidades, escolas, professores, coordenadores e alunos
- Organização de oficinas por região e escola
- Lançamento de presença e desempenho dos alunos
- Formação de turmas
- Acompanhamento pedagógico descentralizado por coordenadores

Este repositório é utilizado para fins internos e demonstração técnica da arquitetura do sistema.

---

## 🛠️ Tecnologias utilizadas

- PHP 8.2+
- Laravel 11
- Laravel Breeze (Blade)
- SQLite (para prototipagem)
- Tailwind CSS
- Vite
- Composer & Artisan
- Node.js

---

## ⚙️ Rodando localmente

> Requisitos: PHP, Composer, Node.js, SQLite

```bash
# Clonar o repositório
git clone git@github.com:gabriel-quina/mgp-plus.git
cd mgp-plus

# Instalar dependências
composer install
npm install && npm run dev

# Configurar ambiente
cp .env.example .env
php artisan key:generate
touch database/database.sqlite

# Rodar as migrations
php artisan migrate

# Iniciar o servidor local
php artisan serve

