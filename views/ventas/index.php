<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'En venta';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$js = <<<EOF

$('#busquedaJuegosNombre').keyup(function (){
  var texto = $('#busquedaJuegosNombre').val();
  var tituloRegExp = new RegExp('.*' + texto + '.*', "i");
  $('.juego').each(function (){
    var nombreJuego = this.attributes.name.value;
    if(!tituloRegExp.test(nombreJuego)){
      this.style.display = "none";
    } else {
      this.style.display = "";
    }
  });
});

$('#busquedaProductosNombre').keyup(function (){
  var texto = $('#busquedaProductosNombre').val();
  var tituloRegExp = new RegExp('.*' + texto + '.*', "i");
  $('.producto').each(function (){
    var nombreProducto = this.attributes.name.value;
    if(!tituloRegExp.test(nombreProducto)){
      this.style.display = "none";
    } else {
      this.style.display = "";
    }
  });
});

EOF;
$this->registerJs($js);
?>
<style media="screen">
    * {
        box-sizing: border-box;
    }

    .column {
        float: left;
        width: 50%;
        padding: 10px;
    }

    #tablaCopias {
        background-color: #b5bcc9;
    }

    #tablaProductos {
        background-color: #c9bbb5;
    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }
</style>
<div class="ventas-index">

    <?php
        // var_dump(sizeof($dataProvider->getModels()));
    ?>

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Ventas', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="row">
        <div class="column">
            <br>

            <input type="text" id="busquedaJuegosNombre" placeholder="Buscar juegos por nombre">

            <br><br>

            <h1>Juegos</h1>

            <br>

            <table class="table" id="tablaCopias">
              <tr>
                <th>Copia</th>
                <th>Géneros</th>
                <th>Usuario</th>
                <th>En venta desde</th>
                <th>Precio 2ª Mano</th>
                <th>Acciones</th>
              </tr>
                <?php
                    foreach ($dataProvider->getModels() as $venta):
                        if($venta->copia === null){
                            continue;
                        }
                        // implode(" ,", $venta->copia->juego->etiquetas)
                ?>
                  <tr class="juego" name="<?= $venta->copia->juego->titulo ?>">
                    <td><?= $venta->copia->juego->titulo ?></td>
                    <td>
                      <?php
                        foreach ($venta->copia->juego->etiquetas as $etiqueta) {
                          echo $etiqueta->nombre . ", ";
                        }
                      ?>
                    </td>
                    <td><?= $venta->vendedor->nombre ?></td>
                    <td><?= Yii::$app->formatter->asRelativeTime($venta->created_at) ?></td>
                    <td><?= Yii::$app->formatter->asCurrency($venta->precio) ?></td>
                    <td>
                        <?= Html::a('Editar', ['/ventas/update', 'id' => $venta->id], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('Retirar', ['/ventas/delete', 'id' => $venta->id], [
                              'class' => 'btn btn-danger',
                              'data' => [
                                  'confirm' => '¿Seguro que quieres retirar esta copia?',
                                  'method' => 'post',
                              ],
                          ]) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="column">
            <br>

            <input type="text" id="busquedaProductosNombre" placeholder="Buscar productos por nombre">

            <br><br>

            <h1>Productos</h1>

            <br>

            <table class="table" id="tablaProductos">
              <tr>
                <th>Producto</th>
                <th>Usuario</th>
                <th>En venta desde</th>
                <th>Precio 2ª Mano</th>
                <th>Acciones</th>
              </tr>
                <?php
                    foreach ($dataProvider->getModels() as $venta):
                        if($venta->producto === null){
                            continue;
                        }
                ?>
                  <tr class="producto" name="<?= $venta->producto->nombre ?>">
                    <td><?= $venta->producto->nombre ?></td>
                    <td><?= $venta->vendedor->nombre ?></td>
                    <td><?= Yii::$app->formatter->asRelativeTime($venta->created_at) ?></td>
                    <td><?= Yii::$app->formatter->asCurrency($venta->precio) ?></td>
                    <td>
                        <?= Html::a('Editar', ['/ventas/update', 'id' => $venta->id], ['class' => 'btn btn-info']) ?>
                        <?= Html::a('Retirar', ['/ventas/delete', 'id' => $venta->id], [
                              'class' => 'btn btn-danger',
                              'data' => [
                                  'confirm' => '¿Seguro que quieres retirar esta copia?',
                                  'method' => 'post',
                              ],
                          ]) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

</div>
