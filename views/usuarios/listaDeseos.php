<?php
// use yii\helpers\Html;

use yii\widgets\ListView;

$js = <<<EOF
$('document').ready(function(){
  $( "#sortable" ).sortable();
  $( "#sortable" ).disableSelection();
});
EOF;
$this->registerJs($js);
?>

<?= ListView::widget([
    'dataProvider' => $deseadosProvider,
    'itemOptions' => ['class' => 'item'],
    'itemView' => function ($model, $key, $index, $widget) {
        <table>
          <tr>
            <th></th>
          </tr>
        </table>
    },
]) ?>