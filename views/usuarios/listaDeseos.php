<?php
// use yii\helpers\Html;

use yii\helpers\Html;
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

    var nuevoOrden = [];
    nuevoOrden[0] = null;
    var contador = 1;
    var botonPrimeraVez = true;
    
    $('#botonOrdenar').click(function (e){
        e.preventDefault();
        $('#listaJuegos').toggle();
        $('#tablasOrden').toggle();
        $('#botonGuardar').toggle();
        if (botonPrimeraVez) {
            alert('Ordena los juegos añadiendo segun tu preferencia');
            botonPrimeraVez = false;
        }
    });

    $('.botonOrden').click(function (e){
        e.preventDefault();
        // console.log(e.currentTarget.parentElement.parentElement);
        var jId = e.currentTarget.parentElement.parentElement.dataset.juegoid;
        var jOr = e.currentTarget.parentElement.parentElement.dataset.juegoorden;
        var jNo = e.currentTarget.parentElement.parentElement.dataset.juegonombre;

        nuevoOrden[contador] = Number(jId);
        contador++;

        console.log(nuevoOrden);
        var nuevaFila = '<tr><td>' + jOr + '</td><td>' + jNo + '</td></tr>';

        $("#tablaDespues").append(nuevaFila);
        e.currentTarget.parentElement.parentElement.style.display = "none";
    });

    $('#botonGuardar').click(function (e){
        e.preventDefault();
        if (nuevoOrden.length != $totalJuegos+1) {
            alert('Primero ordena la lista completa!');
        } else {
            //AJAX con nuevo orden
            nuevoOrdenJson = JSON.stringify(nuevoOrden);
            $.ajax({
                method: 'POST',
                url: '$url',
                data: {uId: $uId, nO: nuevoOrden},
                dataType: 'json',
                success: function(result){
                  if (result) {
                    window.location = '$url2';
                  } else {
                    alert('No ha funcionado, intentalo de nuevo mas tarde');
                  }
                }
              });
        }
    });
}); 
SCRIPT;
$this->registerJS($js);
?>

<h1>Lista de Deseos de <?= $usuario->nombre ?></h1>

<?= Html::a('Ordenar Lista', '#', ['class' => 'btn btn-info', 'id' => 'botonOrdenar', 'hidden' => (Yii::$app->user->id != $uId)]) ?>
<?= Html::a('Guardar Lista', '#', ['class' => 'btn btn-success ml-2', 'id' => 'botonGuardar']) ?>

<div id="tablasOrden" class="row mt-2 mb-2">
    <div class="col-md-4">
        <h3>Orden antes</h3>
        <table border="1" align="left" class="table">
            <tr>
                <th>Orden</th>
                <th>Juego</th>
            </tr>
            <?php foreach ($deseadosProvider->getModels() as $model) { ?>
                <tr
                name="juegoDeseado"
                data-juegoId="<?= $model->juego->id ?>"
                data-juegoOrden="<?= $model->orden?>"
                data-juegoNombre="<?= $model->juego->titulo ?>"
                >
                    <td name="ordenJuego"><?= $model->orden ?></td>
                    <td name="nombreJuego"><?= $model->juego->titulo ?></td>
                    <td><button class="botonOrden"><span class="glyphicon glyphicon-arrow-right"></span></button></td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <div class="col-md-4">
        <h3>Orden Despues</h3>
        <table border="1" id="tablaDespues" class="table">
            <tr>
                <th>Orden</th>
                <th>Juego</th>
            </tr>
        </table>
    </div>
</div>

<div id="listaJuegos">

    <?= ListView::widget([
        'dataProvider' => $deseadosProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            $urlImagen = 'https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fcdn.icon-icons.com%2Ficons2%2F510%2FPNG%2F512%2Fgame-controller-b_icon-icons.com_50382.png&f=1&nofb=1¡';
            ?>
            <h3><?= Html::encode($model->juego->titulo) ?></h3>
            <div class="row">
                <div class="col-md-1">
                    <h1><?= Html::encode($model->orden) ?></h1>
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
            </div>
            <hr>
            <?php
        },
        ]) ?>
</div>
