#!/bin/bash

##XDEBUG_CONFIG
export XDEBUG_CONFIG="remote_autostart=1 remote_enable=1 remote_mode=req remote_port=9001 remote_connect_back=0"
##
php php.php "$@"
