#!/bin/bash

if [ -z "$1" ]; then
    echo "URL base não foi fornecida. Uso correto: $0 <url_base> <token>"
    exit 1
fi

if [ -z "$2" ]; then
    echo "Token não foi fornecido. Uso correto: $0 <url_base> <token>"
    exit 1
fi

export CRON_URL_BASE=$1
export CRON_TOKEN_ENV=$2

CRON_FILE_PATH=./cronfile

CRON_JOB=$(cat $CRON_FILE_PATH)

# Substituir a variável URL no cronjob pelo valor real
CRON_JOB=${CRON_JOB/\$CRON_URL_BASE/$CRON_URL_BASE}

# Substituir a variável Token no cronjob pelo valor real
CRON_JOB=${CRON_JOB/\$CRON_TOKEN_ENV/$CRON_TOKEN_ENV}

# Verificar se a tarefa já existe
if crontab -l | grep -q "$CRON_JOB"; then
    echo "Tarefa cron já existente:"
else
    (echo -e "$(crontab -l)\n$CRON_JOB" | crontab -)
    echo "Tarefa cron adicionada:"
fi

echo "$CRON_JOB"
