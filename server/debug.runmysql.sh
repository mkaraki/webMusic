#!/bin/bash
docker_pwd=`pwd`
docker run --rm -it --network host -e MYSQL_ROOT_PASSWORD=my-secret-pw -e MYSQL_DATABASE=webmusic -v "$docker_pwd/_debugtmp/mysqlpers:/var/lib/mysql" -p 3306:3306 mysql:latest