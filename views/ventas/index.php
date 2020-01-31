<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\VentasSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'En venta';
$this->params['breadcrumbs'][] = $this->title;

$url2 = Url::to(['filtra-copias']);
?>
<?php
$js = <<<EOF

function actualizarLista(nombre, genero){
  console.log(nombre, "\t", genero);

  $.ajax({
    method: 'GET',
    url: '$url2',
    data: {nombre, genero},
      success: function(result){
        if (result) {
          $('#tablacopias').html(result);
        } else {
          alert('No existen Copias con esas caracteristicas');
        }
      },
      error: function(result){
        // alert(result);
      }
  });

}

$('#busquedaJuegosNombre').keyup(function (){
  if (typeof temporizador !== 'undefined') {
    clearTimeout(temporizador);
  }
  var texto = $('#busquedaJuegosNombre').val();
  var genero = $('#busquedaJuegosGenero option:selected').text();
  // var tituloRegExp = new RegExp('.*' + texto + '.*', "i");

  temporizador = setTimeout(actualizarLista, 500, texto, genero);

  // $('.juego').each(function (){
  //   var nombreJuego = this.attributes.name.value;
  //   if(!tituloRegExp.test(nombreJuego)){
  //     this.style.display = "none";
  //   } else {
  //     this.style.display = "";
  //   }
  // });
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
      <div class="col">
        <h1>Juegos</h1>
        <div class="col">
          <h3>Filtros</h3>
          <label for="busquedaJuegosNombre">Nombre:</label>
          <input type="text" id="busquedaJuegosNombre">
          <br>
          <label for="busquedaJuegosGenero">Géneros: </label>
          <select id="busquedaJuegosGenero">

            <option disabled selected>-- selecciona genero--</option>
            <?php foreach ($generos as $genero): ?>
              <option value="<?= $genero->id ?>"><?= $genero->nombre ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col">
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
            
            // return $this->renderAjax('vistaCopias', [
            //   'listaCopias' => $dataProvider->getModels(),
            // ]);

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
            <?php endforeach; ?>
          </table>
        </div>
      </div>

      <?php /* \Yii::debug($dataProvider) */?>

      <div class="col">
          <h1>Productos</h1>

          <div class="col">
            <label for="busquedaProductosNombre"></label>
            <input type="text" id="busquedaProductosNombre">

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

</div>
