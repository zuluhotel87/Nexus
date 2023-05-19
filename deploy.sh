#!/bin/bash

# Constants
CYAN='\033[0;36m'

# Usage function
function usage {
  echo "Usage: $0 [--git] [--backup] [--docker] [--deps] [--permissions] [--restart]"
  echo ""
  echo "Options:"
  echo "  --git          Update code from Git repository"
  echo "  --backup       Backup database and attachments"
  echo "  --docker       Rebuild Docker containers"
  echo "  --deps         Clean up dependencies"
  echo "  --permissions  Set file permissions"
  echo "  --restart      Restart backend services"
  exit 1
}

# Check for input parameter
if [[ "$1" == "--help" ]]; then
  usage
fi

# Get root directory
ROOT_DIR="$(pwd)"

# Get user to wich we assign permissions
source .env
TERMINAL_USER="$TERMINAL_USER"
APP_NAME="$APP_NAME"
APP_ENV="$APP_ENV"

# Set -e to exit immediately on error
set -e

docker exec -it $APP_NAME-php php artisan down

while [[ $# -gt 0 ]]; do
    case $1 in
        --git)
            echo -e "${CYAN}"
            echo "Updating code from Git repository"
            echo ""
            git config --global --add safe.directory /home/ubuntu/$APP_NAME
            git reset --hard
            git fetch
            git pull
            ;;

        --backup)
            echo -e "${CYAN}"
            echo "Backing up database and attachments"
            echo ""
            bash .crons/backupAttachments.sh
            bash .crons/backupDB.sh
            ;;

        --docker)
            echo -e "${CYAN}"
            echo "Rebuilding Docker containers"
            echo ""
            docker-compose down
            docker system prune --all --force --volumes
            docker-compose up -d
            ;;

        --deps)
            echo -e "${CYAN}"
            echo "Cleaning up dependencies"
            echo ""
            chmod -R 777 $ROOT_DIR
            rm -rf vendor/*
            docker exec -it $APP_NAME-php bash -c "composer install --optimize-autoloader --no-dev"
            rm -rf node_modules/*
            docker exec -it $APP_NAME-node npm install
            docker exec -it $APP_NAME-node npm run build
            ;;

        --restart)
            echo -e "${CYAN}"
            echo "Restarting backend services"
            echo ""
            docker-compose down
            docker-compose up -d
            service cron reload
            service cron restart
            ;;

        --cleanup)
            echo -e "${CYAN}"
            echo "Cleaning up and optimizing instance"
            echo ""
            sudo apt clean
            sudo apt autoremove --assume-yes
            sudo rm -rf /tmp/* /var/tmp/*
            sudo find /var/backups -type f -mtime +30 -exec rm {} +
            ;;
        *)
            usage
            ;;
    esac
    shift
done

# Setting file permissions
echo -e "${CYAN}"
echo "Setting file permissions"
echo ""
find $ROOT_DIR -type f ! -path "$ROOT_DIR/.data/*" -exec chown $TERMINAL_USER:$TERMINAL_USER {} \;
find $ROOT_DIR -type d ! -path "$ROOT_DIR/.data/*" -exec chown $TERMINAL_USER:$TERMINAL_USER {} \;
find $ROOT_DIR -type f ! -path "$ROOT_DIR/.data/*" -exec chmod 664 {} \;
find $ROOT_DIR -type d ! -path "$ROOT_DIR/.data/*" -exec chmod 775 {} \;
chmod -R 777 storage bootstrap/cache
usermod -aG $TERMINAL_USER $USER
if [ "$APP_ENV" = "local" ]; then
    chmod -R 777 node_modules vendor
fi

# Migrate database and clear cache
echo -e "${CYAN}"
echo "Migrating database and clearing cache"
echo ""
docker exec $APP_NAME-php php artisan storage:link
docker exec -it $APP_NAME-php php artisan migrate --force
docker exec -it $APP_NAME-php php artisan msgraph:keep-alive

docker exec -it $APP_NAME-php php artisan cache:clear
docker exec -it $APP_NAME-php php artisan config:clear
docker exec -it $APP_NAME-php php artisan view:clear
docker exec -it $APP_NAME-php php artisan clear-compiled
docker exec -it $APP_NAME-php php artisan route:trans:clear

if [ "$APP_ENV" != "local" ]; then
    docker exec -it $APP_NAME-php php artisan config:cache
    docker exec -it $APP_NAME-php php artisan route:trans:cache
    docker exec -it $APP_NAME-php php artisan view:cache
    docker exec -it $APP_NAME-php php artisan event:cache
fi

docker exec -it $APP_NAME-php php artisan up
