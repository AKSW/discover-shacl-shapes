#!/bin/bash

SCRIPT=`realpath $0`
WORK_DIR=`dirname $SCRIPT`

docker run \
    -v $WORK_DIR/../:/var/www/html \
    -v $WORK_DIR/../docker-data/:/shsrv/docker-data \
    -p 7000:80 \
    shsrv
