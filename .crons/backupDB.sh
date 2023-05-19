#!/bin/bash

PROJECT_DIR="$( dirname -- "$BASH_SOURCE"; )";
BACKUP_DIR="$PROJECT_DIR/../.backups/";
BACKUP_FILEPATH=$BACKUP_DIR"db-$(date +"%y-%m-%d-%H%M%S")".tar.gz

source $PROJECT_DIR"/../.env";

sudo docker exec RPEQ-$DB_HOST bash -c "exec mysqldump --databases ${DB_DATABASE} -hRPEQ-$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD" > $BACKUP_FILEPATH;
