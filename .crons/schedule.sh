#!/bin/bash

docker exec RPEQ-php php artisan schedule:run
sudo docker exec RPEQ-php php artisan msgraph:keep-alive
