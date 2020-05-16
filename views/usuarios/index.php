<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsuariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;
$url = Url::to(['usuarios/index']);

$js = <<<SCRIPT
$(document).ready(function (){
    actualizarIndex();
});

function actualizarIndex(){
    $.ajax({
    method: 'GET',
    url: '$url',
    data: {},
        success: function(result){
            if (result) {
                $('#gridUsuarios').html(result);
            } else {
                alert('Ha habido un error con la lista de usuarios');
            }
        }
    });
  }
SCRIPT;

$this->registerJs($js);
?>
<div class="usuarios-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::textInput('textoInput') ?>

    <div id="gridUsuarios">

    </div>
</div>
