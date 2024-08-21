#!/bin/bash
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data public
sudo chmod -R 777 storage
sudo chmod -R 777 .env
