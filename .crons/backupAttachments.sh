#!/bin/bash

PROJECT_DIR="$( dirname -- "$BASH_SOURCE"; )";
BACKUP_DIR="$PROJECT_DIR/../.backups/";
BACKUP_FILEPATH=$BACKUP_DIR"attachments-$(date +"%y-%m-%d-%H%M%S")".tar.gz

source $PROJECT_DIR"/../.env";

tar -cv "$ATTACHMENTS_PATH" | gzip > $BACKUP_FILEPATH
