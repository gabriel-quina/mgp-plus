#!/bin/bash
#
# Script para encerrar os servidores de desenvolvimento Laravel e Vite/Mix
# com base na porta (PID File).
#
# Uso: ./stop-dev.sh [port]
# Ex:  ./stop-dev.sh 8080

# --- 1. CONFIGURAÇÃO DE PORTA ---
if [ -z "$1" ]; then
    # Se nenhuma porta for fornecida, assume a porta padrão do Artisan.
    PORT="8000"
else
    PORT="$1"
fi

echo "Tentando encerrar servidores usando a porta: $PORT"

# --- 2. CONFIGURAÇÃO DE PID FILES E LOGS ---
ARTISAN_PID_FILE="/tmp/laravel_artisan_serve_${PORT}.pid"
NPM_PID_FILE="/tmp/laravel_npm_dev_${PORT}.pid"
ARTISAN_LOG="/tmp/laravel_artisan_${PORT}.log"
NPM_LOG="/tmp/laravel_npm_${PORT}.log"

# --- 3. ENCERRAR ARTISAN SERVE ---
if [ -f $ARTISAN_PID_FILE ]; then
    ARTISAN_PID=$(cat $ARTISAN_PID_FILE)
    # Tenta matar o processo
    if kill $ARTISAN_PID 2>/dev/null; then
        echo "Encerrando Servidor Laravel (PID: $ARTISAN_PID)... OK"
    else
        echo "Aviso: Processo Artisan com PID $ARTISAN_PID não encontrado ou já encerrado."
    fi
    # Remove o arquivo PID e o log
    rm -f $ARTISAN_PID_FILE
    rm -f $ARTISAN_LOG
else
    echo "Servidor Laravel na porta $PORT não está rodando (arquivo PID não encontrado)."
fi

# --- 4. ENCERRAR NPM RUN DEV ---
if [ -f $NPM_PID_FILE ]; then
    NPM_PID=$(cat $NPM_PID_FILE)
    # Tenta matar o processo
    if kill $NPM_PID 2>/dev/null; then
        echo "Encerrando Frontend Watcher (PID: $NPM_PID)... OK"
    else
        echo "Aviso: Processo Frontend com PID $NPM_PID não encontrado ou já encerrado."
    fi
    # Remove o arquivo PID e o log
    rm -f $NPM_PID_FILE
    rm -f $NPM_LOG
else
    echo "Frontend Watcher na porta $PORT não está rodando (arquivo PID não encontrado)."
fi

echo ""
echo "Encerramento concluído para a porta $PORT."
