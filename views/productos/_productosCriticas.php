<?php

use yii\bootstrap4\Html;
?>
<div class="criticas-view">

    <table border="1">
        <tr>
            <th>Usuario</th>
            <th>Opinión</th>
            <th>Valoración</th>
        </tr>
        <tr>
            <td><?= $model->usuario->nombre ?></td>
            <td><?= $model->opinion ?></td>
            <td><?= $model->valoracion ?></td>
        </tr>
    </table>

</div>
