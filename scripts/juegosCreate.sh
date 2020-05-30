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

        descripcionJuego=`awk '/<div class="game_description_snippet">/,/<\/div>/' .paginaJuego.html | cut -d"<" -f1 | sed -n '1!p'`

        fechaLan=`awk '/<div class="date">/,/<\/div>/' .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`
        fechaDia=`echo ${fechaLan:0:2}`
        mesSinFormato=`echo $fechaLan | cut -d" " -f2 | cut -d"," -f1`
        fechaAnio=`echo $fechaLan | cut -d" " -f3`
        case $mesSinFormato in
          Ene|Jan ) fechaMes=01;;
          Feb ) fechaMes=02;;
          Mar ) fechaMes=03;;
          Abr|Apr ) fechaMes=04;;
          May ) fechaMes=05;;
          Jun ) fechaMes=06;;
          Jul ) fechaMes=07;;
          Ago|Aug ) fechaMes=08;;
          Sep ) fechaMes=09;;
          Oct ) fechaMes=10;;
          Nov ) fechaMes=11;;
          Dic|Dec ) fechaMes=12;;
        esac
        fechaFormateada="${fechaAnio}-${fechaMes}-${fechaDia}"

        dev=`awk '/<div class="summary column" id="developers_list">/,/<.div>/' .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`
        # dev=`awk '/<div class="summary column" id="developers_list">/,/<\/div>/' .paginaJuego.html | awk '/<a.*>/,/<.a>/'`

        pub=`grep -m1 'publisher' .paginaJuego.html | cut -d">" -f2 | cut -d"<" -f1`

        edad_minima=3

        echo "Titulo: " ${nombreJuego}
        echo "Descripcion: " ${descripcionJuego}
        echo "FechaLanzamiento: " ${fechaFormateada}
        echo "Desarrolladora: " ${dev}
        echo "Editora: " ${pub}

        sudo -u postgres psql -d gamesandfriends -c "INSERT INTO juegos (titulo, descripcion, fechaLan, dev, publ, edad_minima) VALUES ('${nombreJuego}', '${descripcionJuego}', '${fechaFormateada}', '${dev}', '${pub}', ${edad_minima});"
        # 2>/dev/null
    ;;
    nintendo )
        nombreJuego=`awk '/<title>/' .paginaJuego.html | cut -d">" -f2 | cut -d"|" -f1`
        nombreFormatoUrl=`echo $nombreJuego | tr " " +`

        {
            wget -O .busquedaBing.html https://bing.com/search?q=$nombreFormatoUrl
        } &> /dev/null
        descripcion=`grep -oP '/<p class="b_paractl">.*<.p>/' .busquedaBing.html`
        # Trabajando en la descripcion del juego por parte de la busqueda de bing

        fechaLan=`awk '/Fecha de lanzamiento:/' .paginaJuego.html | cut -d":" -f2 | cut -d"<" -f1`
        fechaFormateada=${fechaLan:7:11}${fechaLan:3:4}${fechaLan:1:2}

        dev=`awk '/class="game_info_title">Desarrollador<.p>/,/"game_info_text">.*<.p>/' .paginaJuego.html | awk '/text">/,/<.p>/' | cut -d">" -f2 | cut -d"<" -f1`
        if [[ $dev == "" ]]; then
            dev="Nintendo"
        fi

        pub=`awk '/class="game_info_title">Distribuidor<.p>/,/"game_info_text">.*<.p>/' .paginaJuego.html | awk '/text">/,/<.p>/' | cut -d">" -f2 | cut -d"<" -f1`

        echo $nombreJuego
        echo $descripcion
        echo $fechaFormateada
        echo $dev
        echo $pub
    ;;
    playstation ) echo "es un enlace de la tienda de playstation";;
    microsoft ) echo "es un enlace de la tienda de xbox";;
    * ) echo "de donde $\#@%! es esto?";;
esac

rm .paginaJuego.html .urlJuego.html .busquedaBing.html
