<?php

use yii\bootstrap4\Html;

?>

<tr>
  <th>Copia</th>
  <th>Géneros</th>
  <th>Usuario</th>
  <th>En venta desde</th>
  <th>Precio 2ª Mano</th>
  <th>Acciones</th>
</tr>

<?php

foreach ($listaVentas as $venta):
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
