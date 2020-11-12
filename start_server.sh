#!/bin/bash

php bin/console doctrine:schema:validate 2>/dev/null
if [[ $? != 0 ]]; then
    php bin/console doctrine:schema:update --force
    if [[ $? != 0 ]]; then
        echo "Couldn't connect to Database"
        sleep 5
        exit 1
    fi
    if [ -z $COCKPIT_EMAIL ]; then
        COCKPIT_EMAIL="admin@localhost"
    fi
    if [ -z $COCKPIT_PASSWORD_FILE ]; then
        COCKPIT_PASSWORD=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 13)
        echo "Admin password is \"$COCKPIT_PASSWORD\""
    else
        COCKPIT_PASSWORD=$(cat $COCKPIT_PASSWORD_FILE)
    fi
    printf "yes\nyes\n$COCKPIT_EMAIL\n$COCKPIT_PASSWORD\n" > tmp.txt
    php bin/console app:init < tmp.txt
    rm tmp.txt
    php bin/console doctrine:schema:validate
    if [[ $? != 0 ]]; then
    	echo "Error validating database schema!"
    	exit 1
    fi
fi
php bin/console server:run 0.0.0.0:8000
