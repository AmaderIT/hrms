#!/bin/bash
sudo docker exec $1 sh -c "chown -R www-data:www-data storage && chown -R www-data:www-data public && chmod -R 777 storage && chmod -R 777 .env"
