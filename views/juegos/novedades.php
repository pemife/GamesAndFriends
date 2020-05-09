<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JuegosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Novedades';
$this->params['breadcrumbs'][] = $this->title;

$uId = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
$js = <<<script
var slideIndex = 0;
showDivs(slideIndex);

function plusDivs(n) {
    showDivs(slideIndex += n);
    console.log("plusDivs");
    console.log(slideIndex);
}

$(document).ready(function(){
    $("#flechaIzda").on("click", function(){
        plusDivs(-1);
    });
    $("#flechaDcha").on("click",function(){
         plusDivs(1);
    });

    $(".selector").on('click', function(){
        var arraySelectores = document.getElementsByName("grupoSelectores");

        slideIndex = parseInt(this.value);
        console.log("selector onclick");
        console.log(slideIndex);
        showDivs(slideIndex);

        for (i=0; i < arraySelectores.length -1; i++) {
            arraySelectores[i].setAttribute("checked", false);
        }
        arraySelectores[slideIndex].setAttribute("checked", true);
    });
});

function showDivs(n) {
  var i;
  var arrayImagenes = document.getElementsByClassName("imagenesJuegos");
  var arrayNombresJuego = document.getElementsByClassName("nombresJuegos");
  var arraySelectores = document.getElementsByClassName("selector");

  if (n > arrayImagenes.length - 1) {slideIndex = 0}
  if (n < 0) {slideIndex = arrayImagenes.length -1}
  console.log("showDivs");
  console.log(slideIndex);

  for (i = 0; i < arrayImagenes.length; i++) {
    arrayImagenes[i].style.display = "none";
    arrayNombresJuego[i].style.display = "none";
  }

  arrayImagenes[slideIndex].style = "width:30%";
  arrayImagenes[slideIndex].style.display = "block";

  arrayNombresJuego[slideIndex].style.display = "block";

  arraySelectores.checked = false;
  arraySelectores[slideIndex].checked = true;
}
$("[name='botonDeseos']").click(anadirDeseos);
function anadirDeseos(e){
  console.log(this.dataset.modelid);
  $.ajax({
    method: 'GET',
    url: '/index.php?r=usuarios/anadir-deseos',
    data: {jId: this.dataset.modelid, uId: $uId},
    success: function(result){
      if (result) {
        alert(result);
      } else {
        alert('NOOOOOOOOOOO');
      }
    }
  });
}
script;

$this->registerJS($js);
?>
<div class="juegos-novedades">
    <style>
        .imagenesJuegos {display:none;}
    </style>

    <h2>Novedades</h2>

    <center>
        <div class="w3-content w3-display-container">
            <?php
            foreach ($juegosProvider->getModels() as $juego) : ?>
                <h3 class="nombresJuegos"><?= Html::encode($juego->titulo) ?></h3>
                <?= Html::a(
                        Html::img(
                            'https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Video-Game-Controller-Icon-D-Edit.svg/480px-Video-Game-Controller-Icon-D-Edit.svg.png',
                            ['class' => 'imagenesJuegos'],
                            ['style' => 'width:30%']
                        ),
                        ['juegos/view', 'id' => $juego->id]
                    ) ?>

            <?php endforeach; ?>
            <!-- <img class="imagenesJuegos" src="imagenJuego.jpg" style="width:30%"> -->
            <button class="w3-button w3-black w3-display-left" id="flechaIzda">&#10094;</button>
            <?php
            for ($i=0; $i < $juegosProvider->getCount(); $i++) {
                echo Html::radio(
                    'grupoSelectores',
                    false,
                    [
                        'class' => 'selector',
                        'value' => $i
                    ]
                );
            }
             ?>
            <button class="w3-button w3-black w3-display-right" id="flechaDcha">&#10095;</button>
        </div>
    </center>

    <?= GridView::widget([
        'dataProvider' => $juegosProvider,
        'columns' => [
            'titulo',
            'fechalan:date',
            'dev',
            'publ',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado} {anadirDeseos}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => false],
                    ['title' => 'ver en mercado']
                  );
                },
                'anadirDeseos' => function ($url, $model, $key) {
                  if (Yii::$app->user->isGuest) {
                    return '';
                  }

                  return Html::a(
                    '<span class="glyphicon glyphicon-heart"></span>',
                    '#',
                    [
                      'title' => 'añadir a tu lista de deseos',
                      'name' => 'botonDeseos',
                      'data' => [
                        'modelId' => $model->id,
                      ]
                    ]
                  );
                },
              ],
            ],
        ],
    ]); ?>

</div>
