<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mercado 2ª Mano';
$this->params['breadcrumbs'][] = $this->title;

$copiasEnVenta = null;
foreach ( $dataProvider->getModels() as $venta ){
  if($venta->copia === null){
    continue;
  } else {
    $copiasEnVenta[] = $venta;
  }
}

$productosEnVenta = null;
foreach ( $dataProvider->getModels() as $venta ){
  if($venta->producto === null){
    continue;
  } else {
    $productosEnVenta[] = $venta;
  }
}
// var_dump($productosEnVenta);
// exit;
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
  if ( $("#tablaCopias .juego").filter(":visible").length == 0){
    $("#trNoHayJuegosNombre").show();
  } else {
    $("#trNoHayJuegosNombre").hide();
  }
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
  if ( $("#tablaProductos .producto").filter(":visible").length == 0){
    $("#trNoHayProductosNombre").show();
  } else {
    $("#trNoHayProductosNombre").hide();
  }
});

$('#busquedaJuegosGenero').change(function (){
    var texto = $('#busquedaJuegosGenero option:selected').text();
    var generoRegExp = new RegExp('.*' + texto + '.*', "i");
    $('.generos').each(function (){
      var generosJuego = this.textContent;
      if(!generoRegExp.test(generosJuego)){
        this.parentNode.style.display = "none";
      } else {
        this.parentNode.style.display = "";
      }
    });
    if ( $("#tablaCopias .juego").filter(":visible").length == 0){
      $("#trNoHayJuegosGenero").show();
    } else {
      $("#trNoHayJuegosGenero").hide();
    }
});

$('#busquedaJuegosGenero').focusout(function (){
    $('#trNoHayJuegosGenero').hide();
});

$('#busquedaJuegosNombre').focusout(function (){
    $('#trNoHayJuegosNombre').hide();
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
        <?= Html::a('Poner en venta Producto/Copia', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="row">
        <div class="column">
            <h1>Juegos</h1>

            <br>

            <input type="text" id="busquedaJuegosNombre" placeholder="Buscar juegos por nombre">
            <br>
            <label for="busquedaJuegosGenero">Filtro géneros: </label>
            <select id="busquedaJuegosGenero">
                <option disabled selected>-- selecciona género--</option>
                <?php foreach ($generos as $genero): ?>
                    <option value="<?= $genero->id ?>"><?= $genero->nombre ?></option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <table class="table" id="tablaCopias">
              <tr>
                <th>Copia</th>
                <th>Géneros</th>
                <th>Usuario</th>
                <th>En venta desde</th>
                <th>Precio 2ª Mano</th>
                <th>Acciones</th>
              </tr>
              <tr id="trNoHayJuegosGenero" style="display: none;">
                <td colspan="6">
                  <center>
                    -- No hay ningun juego de ese género --
                  </center>
                </td>
              </tr>
              <tr id="trNoHayJuegosNombre" style="display: none;">
                <td colspan="6">
                  <center>
                    -- No hay juegos con ese nombre --
                  </center>
                </td>
              </tr>
                <?php
                    if (empty($copiasEnVenta)){
                      ?>
                      <tr>
                        <td colspan="6">
                          <center>
                            -- No hay ningun juego en venta --
                          </center>
                        </td>
                      </tr>
                      <?php
                    } else {

                    foreach ($dataProvider->getModels() as $venta):
                        if($venta->copia === null){
                            continue;
                        }
                ?>
                  <tr class="juego" name="<?= $venta->copia->juego->titulo ?>">
                    <td><?= $venta->copia->juego->titulo ?></td>
                    <td class="generos">
                      <?php
                        $index = 0;
                        $numeroJuegos = count($venta->copia->juego->etiquetas);
                        foreach ($venta->copia->juego->etiquetas as $etiqueta) {
                            if($index == ($numeroJuegos-1)){
                                echo $etiqueta->nombre;
                                continue;
                            }
                            echo $etiqueta->nombre . ", ";
                            $index++;
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
              <?php
                  endforeach;
                }
              ?>
            </table>
        </div>

        <div class="column">
            <h1>Productos</h1>

            <br>

            <input type="text" id="busquedaProductosNombre" placeholder="Buscar productos por nombre">

            <br><br><br>

            <table class="table" id="tablaProductos">
              <tr>
                <th>Producto</th>
                <th>Usuario</th>
                <th>En venta desde</th>
                <th>Precio 2ª Mano</th>
                <th>Acciones</th>
              </tr>
              <tr id="trNoHayProductosNombre">
                <td colspan="5" style="display: none;">
                  <center>
                    -- No hay ningun producto con ese nombre --
                  </center>
                </td>
              </tr>
                <?php
                    if (empty($productosEnVenta)){
                      ?>
                      <tr>
                        <td colspan="6">
                          <center>
                            -- No hay ningun producto en venta --
                          </center>
                        </td>
                      </tr>
                      <?php
                    } else {
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
                <?php
                  endforeach;
                  }
                ?>
            </table>
        </div>
    </div>

</div>
