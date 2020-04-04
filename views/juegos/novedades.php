<?php

use yii\bootstrap4\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\JuegosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Novedades';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="juegos-novedades">
    <style>
        .imagenesJuegos {display:none;}
    </style>

    <h2>Novedades</h2>

    <center>
        <div>
            <?php foreach ($juegosProvider->getModels() as $juego) : ?>
                <img class="imagenesJuegos" src="imagenJuego.jpg" style="width:30%">
            <?php endforeach; ?>

            <button class="w3-button w3-black w3-display-left" onclick="plusDivs(-1)">&#10094;</button>
            <?php
            $length = $juegosProvider->getCount();
            foreach ($juegosProvider->getModels() as $juego) : ?>
                <img class="imagenesJuegos" src="imagenJuego.jpg" style="width:30%">
            <?php endforeach; ?>
            <button class="w3-button w3-black w3-display-right" onclick="plusDivs(1)">&#10095;</button>
        </div>
    </center>

    <script>
        var slideIndex = 1;
        showDivs(slideIndex);

        function plusDivs(n) {
          showDivs(slideIndex += n);
        }

        function showDivs(n) {
          var i;
          var x = document.getElementsByClassName("imagenesJuegos");
          if (n > x.length) {slideIndex = 1}
          if (n < 1) {slideIndex = x.length}
          for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
          }
          x[slideIndex-1].style.display = "block";
        }

    </script>

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $juegosProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'titulo',
            'descripcion:ntext',
            'fechalan',
            'dev',
            'publ',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{view} {vermercado}',
              'buttons' => [
                'vermercado' => function ($url, $model, $key){
                  return Html::a(
                    '<span class="glyphicon glyphicon-shopping-cart"></span>',
                    ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => false],
                    ['title' => 'ver en mercado']
                  );
                },
              ],
            ],
        ],
    ]); ?>

</div>
