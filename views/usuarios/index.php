<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UsuariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
switch ($tipoLista) {
    case 'criticos':
        $tituloParcial = 'críticos';
    break;
    case 'bloqueados':
        $tituloParcial = 'bloqueados';
    break;
    case 'seguidores':
        $tituloParcial = 'seguidores';
    break;
    case 'seguidos':
        $tituloParcial = 'seguidos';
    break;
    default:
        $tituloParcial = '';
}
$this->title = 'Usuarios ' . $tituloParcial;
$this->params['breadcrumbs'][] = $this->title;
$url = Url::to(['usuarios/index-filtrado', 'tipoLista' => $tipoLista]);

$js = <<<SCRIPT
$(document).ready(function (){
    actualizarIndex();
    $("input[name='textoInput']").change(function(e){
        if (typeof cuentaAtras !== 'undefined') {
            clearTimeout(cuentaAtras);
        }
        cuentaAtras = setTimeout(actualizarIndex, 1000);
        $("#cargando").show();
    });
    $("#cargando").hide();
});

cuentaAtras = setTimeout(actualizarIndex, 1000000000);

function actualizarIndex(){
    $("#cargando").hide();
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

    <div id="cargando" align="center">
        <span><img src="https://acs-web.com/blog/wp-content/uploads/2014/09/Loading-circles-acs-rectangles.gif" alt="" width="50" height="50"></span>
    </div>

    <div id="gridUsuarios">

    </div>
</div>
