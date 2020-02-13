#!/bin/bash

{
    wget -O .paginaJuego.html $1
} &> /dev/null

echo $1 > .urlJuego.html

url=`cut -d"." -f2 .urlJuego.html`

case $url in
    steampowered )
        nombreJuego=`egrep "apphub_AppName" .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`
        descripcionJuego=`egrep "game_description_snippet" .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`
        echo $nombreJuego
        echo
        echo $descripcionJuego
    ;;
    nintendo ) echo "es un enlace de nintendo eshop";;
    playstation ) echo "es un enlace de la tienda de playstation";;
    microsoft ) echo "es un enlace de la tienda de xbox";;
    * ) echo "de donde $\#@%! es esto?";;
esac

rm .paginaJuego.html .urlJuego.html
