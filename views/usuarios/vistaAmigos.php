<?php
use yii\helpers\Html;
?>

<div id="tablaAmigos">
  <table class="table table-striped table bordered">
    <tr>
      <th>Amigos: (<?= count($listaAmigos) ?>)</th>
      <?php foreach ($listaAmigos as $amigo) { ?>
        <tr>
          <td id="amigo<?= $amigo->id ?>"><?= Html::a( Html::encode($amigo->nombre), ['view', 'id' => $amigo->id]) ?></td>
        </tr>
      <?php
      }
      // TODO: Controlar limite de amigos mostrados
      ?>
    </tr>
  </table>
</div>
