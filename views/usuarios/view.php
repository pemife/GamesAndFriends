<?php

use yii\grid\GridView;

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

// Yii::debug($model->amigos);

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$enlaceFoto = null; // Cambiar con enlace de AWS

$puedeModificar = (Yii::$app->user->id === 1 || Yii::$app->user->id === $model->id);
$enlaceMod = $puedeModificar ? Url::to(['usuarios/update', 'id' => $model->id]) : '#';
$enlaceBor = $puedeModificar ? Url::to(['usuarios/delete', 'id' => $model->id]) : '#';
$enlacePass = $puedeModificar ? Url::to(['usuarios/cambio-pass', 'id' => $model->id]) : '#';
// $enlaceFoto = $enlaceFoto ? 'enlace' : 'https://www.library.caltech.edu/sites/default/files/styles/headshot/public/default_images/user.png?itok=1HlTtL2d';

$url2 = Url::to(['lista-amigos', 'usuarioId' => $model->id]);

// La lista de amigos solo son visibles para los amigos o para el propio usuario
$esAmigo = Yii::$app->user->isGuest ? false : $model->esAmigo(Yii::$app->user->id);
Yii::debug($esAmigo);
$puedeVerAmigos = $esAmigo || (Yii::$app->user->id == $model->id);
$puedeVerAmigosJS = json_encode($puedeVerAmigos);

$js = <<<EOF
$('document').ready(function(){
  actualizarLista();
});

var esAmigo = $puedeVerAmigosJS;
$('#botonAmistad').click(function(e){
  let mensaje = esAmigo ? "¿Estas seguro de borrar como amigo?" : "¿Estas seguro de añadir como amigo?";
  if(confirm(mensaje)){
    actualizarLista();
  } else {
    e.preventDefault();
  }
});

function actualizarLista(){
  if(esAmigo){
    $.ajax({
      method: 'GET',
      url: '$url2',
      data: {},
        success: function(result){
          if (result) {
            $('#amigosAjax').html(result);
          } else {
            alert('Ha habido un error con la lista de asistentes(2)');
          }
        }
      });
  }
}
EOF;
$this->registerJs($js);

?>

<style>
  .nombreOpciones{
    display: inline-flex;
    justify-content: space-between;
    width: 100%;
  }

  .opciones{
    margin-top: 30px;
  }

  .titulo{
    display: inline-flex;
    justify-content: space-between;
  }

  .flex-container{
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  }

  .flex-container > div {
    flex-grow: 1;
    width: 32%;
    padding: 10px;
  }
</style>
<div class="usuarios-view">
    <div class="nombreOpciones">
        <div class="titulo">
            <h1><?= Html::encode($model->nombre) ?></h1>
            <p>&nbsp;&nbsp;&nbsp;</p>
            <div class="opciones">
              <?php
              if (!Yii::$app->user->isGuest && (Yii::$app->user->id !== $model->id)) {

                if ($model->esAmigo(Yii::$app->user->id)) {
                  echo Html::a('', ['borrar-amigo', 'amigoId' => $model->id], ['id' => "botonAmistad", 'class' =>'glyphicon glyphicon-remove']);
                } else {
                  echo Html::a('', ['mandar-peticion', 'amigoId' => $model->id], ['id' => "botonAmistad", 'class' => 'glyphicon glyphicon-plus']);
                }
              }
              ?>
            </div>
        </div>
        <div class="opciones">
            <span class="dropdown">
                <button class="glyphicon glyphicon-cog" type="button" data-toggle="dropdown" style="height: 30px; width: 35px;"></button>
                <ul class="dropdown-menu pull-right">
                    <li>
                        <?= Html::a('Modificar perfil', $enlaceMod, [
                            'class' => 'btn btn-link',
                            'disabled' => !$puedeModificar,
                            ]) ?>
                    </li>
                    <li>
                        <?= Html::a('Borrar perfil', $enlaceBor, [
                            'class' => 'btn btn-link',
                            'disabled' => !$puedeModificar,
                            'data' => [
                                'confirm' => 'Seguro que quieres borrar el perfil?',
                                'method' => 'POST',
                            ],
                            ]) ?>
                    </li>
                    <li>
                        <?= Html::a('Cambiar contraseña', $enlacePass, [
                            'class' => 'btn btn-link',
                            'disabled' => !$puedeModificar,
                            'data-method' => 'POST',
                            'data-params' => [
                                'tokenUsuario' => $model->token,
                            ],
                            ]) ?>
                    </li>
                    <li>
                        <?= Html::a('Verificar cuenta',
                            [
                              'solicitar-verificacion',
                            ],
                            [
                              'class' => 'btn btn-link',
                              'disabled' => !$puedeModificar || $model->token == null,
                            ]) ?>
                    </li>
                </ul>
            </span>
        </div>
                            </div>
    </div>

    <img src="<?= $enlaceFoto ?>" width="150" height="150">
    <br><br>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nombre',
            'created_at:Date',
            'email:email',
            'biografia:ntext',
            'fechanac:Date',
        ],
    ]) ?>

    <div id="amigosAjax">

    </div>

    <h1>Inventario</h1>

    <div class="row">
      <div class="col">
        <h3>Productos</h3>
        <?= GridView::widget([
          'dataProvider' => $productosProvider,
          'columns' => [
            'nombre',
            'stock',
            [
                'header' => 'Estado',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{estado}',
                'buttons' => [
                    'estado' => function ($url, $model, $key) {
                        //TODO
                    }
                ],
            ],
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{vermercado} {view} {delete}',
              'buttons' => [
                  'vermercado' => function ($url, $model, $key){
                      return Html::a(
                          '<span class="glyphicon glyphicon-shopping-cart"></span>',
                          ['ventas/ventas-item', 'id' => $model->id, 'esProducto' => true],
                          ['title' => 'ver en mercado']
                      );
                  },
                  'view' => function ($url, $model, $key) {
                      return Html::a(
                          '<span class="glyphicon glyphicon-eye-open"></span>',
                          ['productos/view', 'id' => $model->id],
                          ['title' => 'ver producto']
                      );
                  },
                'delete' => function ($url, $model, $key) {
                  if (Yii::$app->user->id == $model->propietario_id){
                    return Html::a(
                      '<span class="glyphicon glyphicon-trash"></span>',
                      [
                          'productos/delete',
                          'id' => $model->id,
                      ],
                      [
                          'data' => [
                            'method' => 'post',
                            'confirm' => '¿Estas seguro de retirar el producto?(Esta accion no se puede deshacer)',
                          ],
                          'title' => 'retirar producto de inventario',
                      ]
                    );
                  }
                  return null;
                }
              ]
            ],
          ],
          ]) ?>
        </div>
        <div class="col">
          <h3>Juegos</h3>
          <?= GridView::widget([
            'dataProvider' => $copiasProvider,
            'columns' => [
              'juego.titulo',
              [
                  'header' => 'Estado',
                  'class' => 'yii\grid\ActionColumn',
                  'template' => '{estado}',
                  'buttons' => [
                      'estado' => function ($url, $model, $key) {
                          //TODO
                      }
                  ],
              ],
              [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{vermercado} {view} {update} {delete}',
                'buttons' => [
                  'view' => function ($url, $model, $key) {
                    return Html::a(
                      '<span class="glyphicon glyphicon-eye-open"></span>',
                      ['copias/view', 'id' => $model->id],
                      ['title' => 'ver copia']
                    );
                  },
                  'vermercado' => function ($url, $model, $key){
                    return Html::a(
                      '<span class="glyphicon glyphicon-shopping-cart"></span>',
                      [
                        'ventas/ventas-item',
                        'id' => $model->id,
                        'esProducto' => false,
                      ],
                      ['title' => 'ver en mercado']
                    );
                  },
                  'update' => function ($url, $model, $key) {
                    if (Yii::$app->user->id == $model->propietario_id){
                      return Html::a(
                        '<span class="glyphicon glyphicon-pencil"></span>',
                        ['ventas/update', 'id' => $model->id],
                        ['title' => 'ver en mercado']
                      );
                    }
                    return null;
                  },
                  'delete' => function ($url, $model, $key) {
                    if (Yii::$app->user->id == $model->propietario_id){
                      return Html::a(
                        '<span class="glyphicon glyphicon-trash"></span>',
                        ['copias/delete', 'id' => $model->id],
                        [
                            'data' => [
                              'method' => 'post',
                              'confirm' => '¿Estas seguro de retirar copia?(Esta accion no se puede deshacer)',
                            ],
                            'title' => 'retirar copia de inventario',
                        ]
                      );
                    }
                    return null;
                  }
                ]
              ],
            ],
            ]) ?>
          </div>
    </div>
</div>
