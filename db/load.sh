#!/bin/sh

BASE_DIR=$(dirname "$(readlink -f "$0")")
if [ "$1" != "test" ]; then
    psql -h localhost -U gamesandfriends -d gamesandfriends < $BASE_DIR/gamesandfriends.sql
fi
psql -h localhost -U gamesandfriends -d gamesandfriends_test < $BASE_DIR/gamesandfriends.sql
