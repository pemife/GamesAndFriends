<?php

use yii\grid\GridView;

use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Usuarios */

$this->title = $model->nombre;
$this->params['breadcrumbs'][] = ['label' => 'Usuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$puedeModificar = (Yii::$app->user->id === 1 || Yii::$app->user->id === $model->id);
$enlaceMod = $puedeModificar ? Url::to(['usuarios/update', 'id' => $model->id]) : 'javascript:void(0)';
$enlaceBor = $puedeModificar ? Url::to(['usuarios/delete', 'id' => $model->id]) : 'javascript:void(0)';
$enlacePass = $puedeModificar ? Url::to(['usuarios/cambio-pass', 'id' => $model->id]) : 'javascript:void(0)';
$enlaceFotos = $puedeModificar ? Url::to(['usuarios/cambio-imagen', 'id' => $model->id]) : 'javascript:void(0)';
$enlaceFondos = $puedeModificar ? Url::to(['usuarios/cambio-fondo', 'id' => $model->id]) : 'javascript:void(0)';

$urlAmigos = Url::to(['lista-amigos', 'usuarioId' => $model->id]);
$urlBloqueados = Url::to(['lista-bloqueados', 'usuarioId' => $model->id]);

// La lista de amigos solo son visibles para los amigos o para el propio usuario
$esAmigo = Yii::$app->user->isGuest ? false : $model->esAmigo(Yii::$app->user->id);
$puedeVerAmigos = $esAmigo || (Yii::$app->user->id == $model->id);
$puedeVerAmigosJS = json_encode($puedeVerAmigos);

// Recomendaciones de usuarios
$tieneRecomendaciones = sizeof($usuariosRecomendadosProvider->getModels());

$js = <<<EOF
$('document').ready(function(){
  $('#bloqueadosAjax').hide();
  actualizarListaAmigos();

  if ($tieneRecomendaciones) {
      setTimeout(function() {
          $('#modalRecomendados').modal('show');
      }, 3000);
  }
});

var esAmigo = $puedeVerAmigosJS;
$('#botonAmistad').click(function(e){
  e.preventDefault();
  let mensaje = esAmigo ? "¿Estas seguro de borrar como amigo?" : "¿Estas seguro de añadir como amigo?";
  if(confirm(mensaje)){
    actualizarLista();
  } else {
    e.preventDefault();
  }
});

$('#botonBloqueados').click(function(e){
  e.preventDefault();
  actualizarListaBloqueados();
  $('#bloqueadosAjax').show();
});

$('#botonEdit').click(function(){
  ventanaAux = window.open('$enlaceFotos', 'aux', 'width=530, height=450');
  ventanaAux.moveBy(350,250);
  ventanaAux.focus();
});

$('#botonFondos').click(function(){
  ventanaAux = window.open('$enlaceFondos', 'aux', 'width=800, height=450');
  ventanaAux.moveBy(350,250);
  ventanaAux.focus();
});

function actualizarListaAmigos(){
  if(esAmigo){
    $.ajax({
      method: 'GET',
      url: '$urlAmigos',
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

function actualizarListaBloqueados(){
  if(esAmigo){
    $.ajax({
      method: 'GET',
      url: '$urlBloqueados',
      data: {},
        success: function(result){
          if (result) {
            $('#bloqueadosAjax').html(result);
          } else {
            alert('Ha ocurrido un error');
          }
        }
      });
  }
}
EOF;
$this->registerJs($js);
?>
<style>
    <?php if (!empty($model->fondo_key)) : ?>
      body{
        background-image: url(<?= $model->urlFondo ?>);
      }
    <?php endif; ?>

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

  .contenedorImagen {
    display: inline-block;
    position: relative ;
  }

  .imagenPerfil {
    transition: 0.3s ;
  }

  .botonImagen {
    position: absolute ;
    top: 50% ;
    left: 50% ;
    transform: translate(-50%, -50%) ;
    opacity: 0 ;
    transition: 0.3s ;
  }

  .contenedorImagen:hover > .imagenPerfil {opacity: 0.6 }
  .contenedorImagen:hover > .botonImagen {opacity: 1}
</style>

<div class="usuarios-view">
    <div class="nombreOpciones">
      <div class="titulo">
          <h1><?= Html::encode($model->nombre) ?> <?= $model->es_critico ? '<span class="glyphicon glyphicon-education" title="Insignia de Crítico de Juegos/Productos"> </span>' : '' ?></h1>
          <p>&nbsp;&nbsp;&nbsp;</p>
          <div class="opciones">
            <?php
            if (!Yii::$app->user->isGuest && (Yii::$app->user->id !== $model->id)) {
                switch ($model->estadoRelacion(Yii::$app->user->id)) {
                    case 1:
                        echo Html::a('', ['borrar-amigo', 'amigoId' => $model->id], ['id' => 'botonAmistad', 'class' =>'glyphicon glyphicon-remove', 'title' => 'Borrar amigo']);
                    break;
                    case 2:
                    case 5:
                        echo Html::a('', ['mandar-peticion', 'amigoId' => $model->id], ['id' => 'botonAmistad', 'class' =>'glyphicon glyphicon-plus', 'title' => 'Mandar peticion de amistad']);
                    break;
                    case 3:
                    break;
                }
            }
            ?>
          </div>
      </div>
        <div class="opciones">
            <span class="dropdown">
                <button 
                  class="glyphicon glyphicon-cog"
                  type="button"
                  data-toggle="dropdown"
                  style="height: 30px; width: 35px;"
                    <?= Yii::$app->user->id == 1 || $model->id == Yii::$app->user->id ? '' : 'hidden' ?>>
                </button>
                <ul class="dropdown-menu pull-right">
                    <li>
                        <?= Html::a('Modificar perfil', $enlaceMod, [
                            'class' => 'btn btn-link',
                            'disabled' => !$puedeModificar,
                            ]) ?>
                    </li>
                    <li>
                        <?= Html::a('Juegos deseados', ['ver-lista-deseos', 'uId' => $model->id], [
                            'class' => 'btn btn-link',
                        ]) ?>
                    </li>
                    <li>
                        <?= Html::a('Juegos ignorados', ['ver-lista-ignorados', 'uId' => $model->id], [
                            'class' => 'btn btn-link',
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
                        <?= Html::a('Cambiar fondo perfil', 'javascript:void(0)', [
                            'class' => 'btn btn-link',
                            'id' => 'botonFondos'
                        ]) ?>
                    </li>
                    <li>
                        <?= Html::a('Añadir géneros preferidos', ['usuarios/anadir-preferencias'], ['class' => 'btn btn-link',]) ?>
                    </li>
                    <?php if ($model->token != null) : ?>
                    <li>
                        <?= Html::a(
                            'Verificar cuenta',
                            [
                              'solicitar-verificacion',
                            ],
                            [
                              'class' => 'btn btn-link',
                              'disabled' => !$puedeModificar && $model->token == null,
                            ]
                        ) ?>
                    </li>
                    <?php endif; ?>
                    <li>
                        <?= Html::a('Ver usuarios bloqueados', 'javascript:void(0)', [
                            'class' => 'btn btn-link',
                            'id' => 'botonBloqueados',
                            'style' => [
                              'color' => 'red',
                            ]
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
                            'style' => [
                              'color' => 'red',
                            ]
                            ]) ?>
                    </li>
                </ul>
            </span>
        </div>
      </div>
    </div>

    <div class="contenedorImagen mb-4 mt-2">
        <?= Html::img(
            $model->urlImagen,
            [
              'class' => 'rounded-circle imagenPerfil',
              'width' => 150,
              'height' => 150,
            ]
        ) ?>
        <div class="botonImagen bg-light rounded-circle p-2">
            <?= Html::a(
                '',
                'javascript:void(0)',
                [
                  'class' => 'glyphicon glyphicon-edit rounded-circle',
                  'id' => 'botonEdit'
                ]
            ) ?>
        </div>
    </div>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'nombre',
            'created_at:Date',
            'email:email',
            'biografia:ntext',
            'fechanac:Date',
            'preferencias',
        ],
    ]) ?>

    <div class="row">
      <div class="col-6" id="amigosAjax">
      </div>
  
      <div class="col-6" id="bloqueadosAjax">
      </div>
    </div>

    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->id != $model->id) { ?>
      <div id="OpcionesUsuario">
            <?php
            if ($model->estaBloqueadoPor(Yii::$app->user->id)) {
                echo Html::a('Desbloquear usuario', ['desbloquear-usuario', 'usuarioId' => $model->id], ['class' => 'btn btn-danger']);
            } else {
                echo Html::a('Bloquear usuario', ['bloquear-usuario', 'usuarioId' => $model->id], ['class' => 'btn btn-danger']);
            }
            if ($model->esAmigo(Yii::$app->user->id)) {
                echo Html::a('Ver lista deseos', ['ver-lista-deseos', 'uId' => $model->id], ['class' => 'btn btn-info ml-2']);
            }
            ?>
      </div>
    <?php } ?>

    <?php
    if ($model->es_critico) {
        echo Html::a('Ver lista de seguidores', ['index-filtrado', 'texto' => false, 'tipoLista' => 'seguidores'], ['class' => 'btn btn-success mb-4 mb-2']);
    }
    echo Html::a('Ver lista de críticos seguidos', ['criticas/index'], ['class' => 'btn btn-success mb-4 mb-2 ml-2']);
    ?>

    <h1>Inventario</h1>

    <div class="row">
      <div class="col">
        <h3>Productos</h3>
        <?= GridView::widget([
          'dataProvider' => $productosProvider,
          'rowOptions' => [
              'itemscope' => true,
              'itemtype' => 'http://schema.org/Product',
          ],
          'columns' => [
            [
              'attribute' => 'nombre',
              'contentOptions' => ['itemprop' => 'name']
            ],
            'stock',
            'estado',
            [
              'class' => 'yii\grid\ActionColumn',
              'template' => '{vender} {view} {delete}',
              'buttons' => [
                  'vender' => function ($url, $model, $key) {
                    if (!$model->estado) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-shopping-cart"></span>',
                            [
                              'ventas/crea-venta-item',
                              'cId' => false,
                              'pId' => $model->id
                            ],
                            ['title' => 'vender']
                        );
                    }
                    return false;
                  },
                  'view' => function ($url, $model, $key) {
                      return Html::a(
                          '<span class="glyphicon glyphicon-eye-open"></span>',
                          ['productos/view', 'id' => $model->id],
                          ['title' => 'ver producto']
                      );
                  },
                  'delete' => function ($url, $model, $key) {
                    if (Yii::$app->user->id == $model->propietario_id) {
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
              'rowOptions' => [
                  'itemscope' => true,
                  'itemtype' => 'http://schema.org/Product',
              ],
              'columns' => [
                [
                    'attribute' => 'juego.titulo',
                    'contentOptions' => ['itemprop' => 'name']
                ],
                'plataforma.nombre:text:Plataforma',
                'estado',
                [
                  'class' => 'yii\grid\ActionColumn',
                  'template' => '{vender} {view} {retirar}',
                  'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>',
                            ['copias/view', 'id' => $model->id],
                            ['title' => 'ver copia']
                        );
                    },
                    'vender' => function ($url, $model, $key) {
                        if (!$model->estado) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-shopping-cart"></span>',
                                [
                                  'ventas/crea-venta-item',
                                  'cId' => $model->id,
                                  'pId' => false
                                ],
                                ['title' => 'vender']
                            );
                        }
                        return false;
                    },
                    'retirar' => function ($url, $model, $key) {
                        if (Yii::$app->user->id == $model->propietario_id) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-trash"></span>',
                                ['copias/retirar-inventario', 'id' => $model->id],
                                [
                                    'data' => [
                                      'method' => 'post',
                                      'confirm' => '¿Estas seguro de retirar copia?(Esta accion no se puede deshacer)',
                                    ],
                                    'title' => 'retirar copia de inventario',
                                    'style' => [
                                        'color' => 'red',
                                    ],
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
    <?php
    Modal::begin([
        'id' => 'modalRecomendados',
    ]);
    ?>
        <div id="contenidoModal">
            <p>Usuarios recomendados segun tus juegos</p>
            <br>
            <?= GridView::widget([
              'dataProvider' => $usuariosRecomendadosProvider,
              'columns' => [
                [
                  'attribute' => 'nombre',
                  'format' => 'raw',
                  'value' => function ($model) {
                      return
                      Html::a(
                          Html::img(
                              $model->urlImagen,
                              [
                              'class' => 'rounded-circle mr-2',
                              'width' => 50,
                              'height' => 50,
                              ]
                          )
                          . $model->nombre,
                          [
                              'usuarios/view',
                              'id' => $model->id
                          ]
                      );
                  }
                ],
                [
                  'class' => 'yii\grid\ActionColumn',
                  'template' => '{amistad}',
                  'buttons' => [
                    'amistad' => function ($url, $model, $key) {
                        return Html::a(
                            '',
                            ['mandar-peticion', 'amigoId' => $model->id],
                            [
                              'class' => 'glyphicon glyphicon-plus',
                              'title' => 'Mandar peticion de amistad a ' . $model->nombre
                            ]
                        );
                    }
                  ]
                ]
              ]]);
            ?>
        </div>

    <?php
    Modal::end();
    ?>

</div>
