#!/bin/bash
sudo docker exec $1 sh -c "php artisan optimize:clear"