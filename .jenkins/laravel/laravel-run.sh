#!/bin/bash
sudo docker run -d -p $2:80 -v $3:/var/www/html/storage -v $4:/var/www/html/public/uploads --restart=always --name $1 $1
