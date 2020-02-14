#!/bin/bash

count=`sudo -u postgres psql -d gamesandfriends -c "SELECT count(*) FROM juegos;" | cut -d"${\n}" -f2`

echo $count
