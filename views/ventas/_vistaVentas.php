<?php



?>

<tr>
  <td><?= $model->producto->nombre ? $model->producto->nombre : $model->copia->juego->titulo ?></td>
  <td><?= $model->vendedor->nombre ?></td>
  <td><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></td>
  <td><?= Yii::$app->formatter->asCurrency($model->precio) ?></td>
</tr>
