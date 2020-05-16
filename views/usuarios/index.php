<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsuariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = $this->title;
$url = Url::to(['usuarios/index-filtrado']);

$js = <<<SCRIPT
$(document).ready(function (){
    actualizarIndex();
    $("input[name='textoInput']").keyup(function(e){
        if (typeof cuentaAtras !== 'undefined') {
            clearTimeout(cuentaAtras);
        }
        cuentaAtras = setTimeout(actualizarIndex, 2000);
    });
});

cuentaAtras = setTimeout(actualizarIndex, 1000000000);

function actualizarIndex(){
    var texto = $("input[name='textoInput']").val();
    $.ajax({
    method: 'GET',
    url: '$url',
    data: {texto: texto},
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

    <?= Html::textInput('textoInput', null, [
        'placeholder' => 'Escriba un email o nombre de usuario',
        'size' => '32',
        'class' => 'form-control mb-3 mt-4'
    ]) ?>


    <div id="gridUsuarios">

    </div>
</div>
