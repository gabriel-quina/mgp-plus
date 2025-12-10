#!/bin/bash
#
# Script para iniciar o servidor de desenvolvimento Laravel e o frontend watcher (Vite/Mix)
# em segundo plano (non-blocking), aceitando HOST e PORT como argumentos.
#
# Uso: ./start-dev.sh [host] [port]
# Ex:  ./start-dev.sh 0.0.0.0 8080

# Define a pasta do projeto (assume que o script está na raiz do Laravel)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

# --- 1. CONFIGURAÇÃO DE HOST E PORTA ---
DEFAULT_HOST="127.0.0.1"
DEFAULT_PORT="8000"

HOST=${1:-$DEFAULT_HOST}
PORT=${2:-$DEFAULT_PORT}
ADDRESS="http://$HOST:$PORT"

# --- 2. CONFIGURAÇÃO DE PID FILES ---
# Armazena os arquivos PID no diretório temporário, mas com o nome do script
# e porta para evitar conflitos se rodar múltiplos projetos.
ARTISAN_PID_FILE="/tmp/laravel_artisan_serve_${PORT}.pid"
NPM_PID_FILE="/tmp/laravel_npm_dev_${PORT}.pid"
ARTISAN_LOG="/tmp/laravel_artisan_${PORT}.log"
NPM_LOG="/tmp/laravel_npm_${PORT}.log"

# --- 3. FUNÇÃO DE INÍCIO DO BACKEND ---
start_backend() {
    echo "Iniciando o servidor Laravel ($ADDRESS) em segundo plano..."

    # Determina se precisa de sudo (geralmente necessário para portas < 1024, como 80)
    SUDO_CMD=""
    if [ "$PORT" -lt 1024 ] && [ "$EUID" -ne 0 ]; then
        SUDO_CMD="sudo -E"
        echo "Aviso: Porta $PORT é privilegiada. Será solicitada a senha de sudo."
        # A flag -E preserva o ambiente (PATH, etc.) para que o php seja encontrado.
    fi

    # Entra no diretório do projeto para garantir que o 'artisan' funcione.
    cd "$PROJECT_DIR"

    # Comando de execução do servidor
    # O '&' executa em segundo plano.
    $SUDO_CMD php artisan serve --host=$HOST --port=$PORT > $ARTISAN_LOG 2>&1 &

    ARTISAN_PID=$!
    echo $ARTISAN_PID > $ARTISAN_PID_FILE

    echo "  > Backend iniciado com PID: $ARTISAN_PID. Host: $HOST, Porta: $PORT."
    echo "  > Log em: $ARTISAN_LOG"
}

# --- 4. FUNÇÃO DE INÍCIO DO FRONTEND ---
start_frontend() {
    echo "Iniciando o frontend watcher (npm run dev) em segundo plano..."

    cd "$PROJECT_DIR"

    # O NPM não requer sudo, mas precisa estar no diretório correto.
    npm run dev > $NPM_LOG 2>&1 &

    NPM_PID=$!
    echo $NPM_PID > $NPM_PID_FILE

    echo "  > Frontend iniciado com PID: $NPM_PID."
    echo "  > Log em: $NPM_LOG"
}


# --- 5. VERIFICAÇÃO E EXECUÇÃO ---

# Verifica se o Artisan está rodando e inicia se não estiver
if [ -f $ARTISAN_PID_FILE ] && kill -0 $(cat $ARTISAN_PID_FILE) 2>/dev/null; then
    echo "Servidor Artisan já está rodando (PID: $(cat $ARTISAN_PID_FILE))."
else
    start_backend
fi

# Verifica se o NPM está rodando e inicia se não estiver
if [ -f $NPM_PID_FILE ] && kill -0 $(cat $NPM_PID_FILE) 2>/dev/null; then
    echo "Frontend Watcher já está rodando (PID: $(cat $NPM_PID_FILE))."
else
    start_frontend
fi

echo ""
echo "=================================================================="
echo "Servidores em execução."
echo "URL de Acesso: $ADDRESS"
echo "Para PARAR, execute: ./stop-dev.sh $PORT"
echo "=================================================================="
