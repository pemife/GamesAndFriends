#!/bin/bash

{
    wget -O .paginaJuego.html $1
} &> /dev/null

echo $1 > .urlJuego.html

url=`cut -d"." -f2 .urlJuego.html`

# formatearFecha() {
#   fechaDia=`cut -d" " -f1 $1`
#   mesSinFormato=`cut -d" " -f2 $1 | cut -d"," -f1`
#   fechaAnio=`cut -d" " -f3 $1`
#   echo $fechaDia
#   echo $mesSinFormato
#   echo $fechaAnio
#   case $mesSinFormato in
#     ENE ) fechaMes=01;;
#     FEB ) fechaMes=02;;
#     MAR ) fechaMes=03;;
#     ABR ) fechaMes=04;;
#     MAY ) fechaMes=05;;
#     JUN ) fechaMes=06;;
#     JUL ) fechaMes=07;;
#     AGO ) fechaMes=08;;
#     SEP ) fechaMes=09;;
#     OCT ) fechaMes=10;;
#     NOV ) fechaMes=11;;
#     DIC ) fechaMes=12;;
#   esac
#   return "$fechaDia/$fechaMes/$fechaAnio"
# }

case $url in
    steampowered )
        nombreJuego=`egrep "apphub_AppName" .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`

        descripcionJuego=`awk '/<div class="game_description_snippet">/,/<\/div>/' .paginaJuego.html | cut -d"<" -f1`

        fechaLan=`awk '/<div class="date">/,/<\/div>/' .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`
        fechaDia=`echo ${fechaLan:0:2}`
        mesSinFormato=`echo $fechaLan | cut -d" " -f2 | cut -d"," -f1`
        fechaAnio=`echo $fechaLan | cut -d" " -f3`
        case $mesSinFormato in
          Ene ) fechaMes=01;;
          Feb ) fechaMes=02;;
          Mar ) fechaMes=03;;
          Abr ) fechaMes=04;;
          May ) fechaMes=05;;
          Jun ) fechaMes=06;;
          Jul ) fechaMes=07;;
          Ago ) fechaMes=08;;
          Sep ) fechaMes=09;;
          Oct ) fechaMes=10;;
          Nov ) fechaMes=11;;
          Dic ) fechaMes=12;;
        esac
        fechaFormateada="$fechaAnio-$fechaMes-$fechaDia"

        dev=`awk '/<div class="summary column" id="developers_list">/,/<\/div>/' .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`

        pub=`awk '/\/publisher\//,/<\/a>/' .paginaJuego.html`
        #Me he quedado aqui

        echo $nombreJuego
        echo $descripcionJuego
        echo $fechaFormateada
        echo $dev
        echo $pub
    ;;
    nintendo ) echo "es un enlace de nintendo eshop";;
    playstation ) echo "es un enlace de la tienda de playstation";;
    microsoft ) echo "es un enlace de la tienda de xbox";;
    * ) echo "de donde $\#@%! es esto?";;
esac

rm .paginaJuego.html .urlJuego.html
