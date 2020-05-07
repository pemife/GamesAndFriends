<?php
use yii\helpers\Html;
?>

<div id="tablaBloqueados">
  <table class="table table-striped table bordered">
    <tr>
      <th colspan="2">Usuarios bloqueados: (<?= count($listaBloqueados) ?>)</th>
      <?php foreach ($listaBloqueados as $bloqueado) { ?>
        <tr>
          <td id="bloqueado<?= $bloqueado->id ?>"><?= Html::encode($bloqueado->nombre) ?></td>
          <td><?= Html::a('', ['desbloquear-usuario', 'usuarioId' => $bloqueado->id], [
            'class' => 'glyphicon glyphicon-ok-circle',
            'title' => 'Desbloquear usuario',
            'data-confirm' => 'Seguro que quieres desbloquear al usuario ' . $bloqueado->nombre . '?',
            ]) ?></td>
        </tr>
      <?php
      }
      // TODO: Controlar limite de amigos mostrados
      ?>
    </tr>
  </table>
</div>
