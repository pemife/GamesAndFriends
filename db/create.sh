#!/bin/sh

if [ "$1" = "travis" ]; then
    psql -U postgres -c "CREATE DATABASE gamesandfriends_test;"
    psql -U postgres -c "CREATE USER gamesandfriends PASSWORD 'gamesandfriends' SUPERUSER;"
else
    sudo -u postgres dropdb --if-exists gamesandfriends
    sudo -u postgres dropdb --if-exists gamesandfriends_test
    sudo -u postgres dropuser --if-exists gamesandfriends
    sudo -u postgres psql -c "CREATE USER gamesandfriends PASSWORD 'gamesandfriends' SUPERUSER;"
    sudo -u postgres createdb -O gamesandfriends gamesandfriends
    sudo -u postgres psql -d gamesandfriends -c "CREATE EXTENSION pgcrypto;" 2>/dev/null
    sudo -u postgres createdb -O gamesandfriends gamesandfriends_test
    sudo -u postgres psql -d gamesandfriends_test -c "CREATE EXTENSION pgcrypto;" 2>/dev/null
    LINE="localhost:5432:*:gamesandfriends:gamesandfriends"
    FILE=~/.pgpass
    if [ ! -f $FILE ]; then
        touch $FILE
        chmod 600 $FILE
    fi
    if ! grep -qsF "$LINE" $FILE; then
        echo "$LINE" >> $FILE
    fi
fi
