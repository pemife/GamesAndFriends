<?php
// use yii\bootstrap4\Html;

use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = 'Lista de deseos';

$totalJuegos = $deseadosProvider->count;
$uId = $usuario->id;
$url = Url::to(['usuarios/ordenar-lista-deseos']);
$url2 = Url::to(['usuarios/ver-lista-deseos', 'uId' => $usuario->id], true);

$js = <<<SCRIPT
$(function(){
    $('#tablasOrden').hide();
    $('#botonGuardar').hide();

    $('#botonGuardar').click(function (e){
        e.preventDefault();
        if (nuevoOrden.length != $totalJuegos) {
            alert('Primero ordena la lista completa!');
        } else {
            //AJAX con nuevo orden
            $.ajax({
                method: 'POST',
                url: '$url',
                data: {uId: $uId, nO: nuevoOrden},
                dataType: 'json',
                success: function(result){
                    console.log(result);
                    if (result) {
                        window.location = '$url2';
                    } else {
                        alert('No ha funcionado, intentalo de nuevo mas tarde');
                    }
                }
              });
        }
    });

    $('#listaJuegos').sortable({
        update: function(e, ui){
            $('#botonGuardar').show();
            nuevoOrden = devolverNuevoOrden();
        }
    });

    $('#listaJuegos').disableSelection();

    function devolverNuevoOrden(){
        var ordenJuegos = new Array();
        var juegos = $('.juego');

        for (var i = 0; i < juegos.length; i++) {
            ordenJuegos.push(juegos[i].dataset.juegoid);
        }

        return ordenJuegos;
    }
}); 
SCRIPT;
$this->registerJS($js);
?>
<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js  " defer></script>

<h1>Lista de Deseos de <?= $usuario->nombre ?></h1>

<p>Puedes ordenar la lista arrastrando los juegos</p>

<?= Html::a('Guardar Orden', 'javascript:void(0)', ['class' => 'btn btn-success ml-2 mb-2', 'id' => 'botonGuardar']) ?>

<div id="listaJuegos">

    <?= ListView::widget([
        'dataProvider' => $deseadosProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            $urlImagen = 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fcdn.icon-icons.com%2Ficons2%2F510%2FPNG%2F512%2Fgame-controller-b_icon-icons.com_50382.png&f=1&nofb=1¡';
            ?>
            <div 
            class="row juego"
            data-juegoId="<?= $model->juego->id ?>"
            data-juegoOrden="<?= $model->orden?>"
            data-juegoNombre="<?= $model->juego->titulo ?>"
            >
                <div class="row col-md-12">
                    <h3><?= Html::encode($model->juego->titulo) ?></h3>
                </div>
                <div class="col-md-1">
                    <div><span class="glyphicon glyphicon-menu-hamburger"></span></div>
                    <div><h1><?= Html::encode($model->orden) ?></h1></div>
                    <div><span class="glyphicon glyphicon-menu-hamburger"></span></div>
                </div>
                <div class="col-md-3">
                    <img src="<?= $urlImagen ?>" width="150" height="125">
                </div>
                <div class="col-md-7">
                    <p><?= Html::encode($model->juego->descripcion) ?></p>
                </div>
                <div class="col-md-1">
                    <?= Html::a(
                        '',
                        [
                            'ventas/ventas-item',
                            'id' => $model->juego->id,
                            'esProducto' => false
                        ],
                        [
                            'class' => 'glyphicon glyphicon-shopping-cart',
                            'title' => 'ver en mercado',
                            ]
                    ) ?>
                    <?php
                    if ($model->usuario->id == Yii::$app->user->id) {
                        echo Html::a(
                            '',
                            [
                                'usuarios/borrar-deseos',
                                'jId' => $model->juego->id,
                                'uId' => Yii::$app->user->id
                            ],
                            [
                                'class' => 'glyphicon glyphicon-remove-circle',
                                'title' => 'Borrar de tu lista de deseados',
                                'data-confirm' => '¿Estas seguro de que quieres borrarlo?',
                            ]
                        );
                    }
                    ?>
                </div>
                <div class="col-md-12"><hr></div>
            </div>
            <?php
        },
        ]) ?>
</ul>
