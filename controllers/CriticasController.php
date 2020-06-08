<?php

namespace app\controllers;

use app\models\Criticas;
use app\models\Productos;
use app\models\ReportesCriticas;
use app\models\Usuarios;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CriticasController implementa las acciones CRUD para el modelo Criticas.
 */
class CriticasController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'update', 'delete', 'index', 'critica-producto'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'index'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            $model = Criticas::findOne(Yii::$app->request->queryParams['id']);
                            if (!Yii::$app->user->isGuest && ($model->usuario_id == Yii::$app->user->id)) {
                                return true;
                            }
                            Yii::$app->session->setFlash('error', '¡No puedes modificar la crítica de otra persona!');
                            return false;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['critica-producto'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes hacer una reseña sin iniciar sesion!');
                                return false;
                            }

                            $producto_id = Yii::$app->request->queryParams['producto_id'];
                    
                            if (Criticas::find()->where(['usuario_id' => Yii::$app->user->id, 'producto_id' => $producto_id])->exists()) {
                                Yii::$app->session->setFlash('error', 'Ya has hecho una reseña de ese producto');
                                return false;
                            }
                    
                            if (!Usuarios::findOne(Yii::$app->user->id)->tieneProducto($producto_id)) {
                                Yii::$app->session->setFlash('error', 'No puedes hacer una reseña de un producto que no tienes');
                                return false;
                            }

                            return true;
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Lista de todos los modelos Criticas de los usuarios críticos a los que sigas.
     * No se permite acceder si no esta logueado.
     *
     * @return string la pagina renderizada
     */
    public function actionIndex()
    {
        $usuario = Usuarios::findOne(Yii::$app->user->id);
        
        $query = Criticas::find()
        ->where(['in', 'usuario_id', $usuario->listaCriticosSeguidosId()])
        ->orderBy('last_update');

        if (!$query->exists()) {
            Yii::$app->session->setFlash('error', '¡No sigues a ningún crítico!');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Muestra el producto/juego del que habla la crítica
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionView($id)
    {
        $critica = Criticas::findOne($id);

        if (empty($critica->producto_id)) {
            return $this->redirect(['juegos/view', 'id' => $critica->juego_id]);
        }
        return $this->redirect(['productos/view', 'id' => $critica->producto_id]);
    }

    /**
     * Crea un modelo nuevo de Críticas.
     * Si la creación es exitosa, redirecciona a criticas/view, que muestra
     * el producto/juego del que habla la crítica.
     * Esta accion está limitada a los usuarios logueados
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'No puedes hacer una reseña sin iniciar sesion!');
            return $this->redirect(['site/login']);
        }

        $model = new Criticas();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualiza un modelo existente de Críticas.
     * Si se actualiza con éxito, redireciona a la pagina de vista del modelo.
     * Si la actualización es exitosa, redirecciona a criticas/view, que muestra
     * el producto/juego del que habla la crítica.
     * Esta accion está limitada solo al usuario creador de la critica.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se puede encontrar
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->esCriticaProducto()) {
            return $this->render('criticaProducto', [
                'model' => $model,
                'producto' => Productos::findOne($model->producto_id),
            ]);
        }

        return $this->render('criticaJuego', [
            'model' => $model,
        ]);
    }

    /**
     * Borra un modelo existente de Críticas.
     * Si el borrado es exitoso, redireccionará al producto/juego del que hablaba.
     * Esta accion está limitada solo al usuario creador de la critica.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->esCriticaProducto()) {
            $url = ['productos/view', 'id' => $model->producto_id];
        } else {
            $url = ['juegos/view', 'id' => $model->juego_id];
        }

        $model->delete();

        return $this->redirect($url);
    }

    /**
     * Crea un reporte sobre la crítica, puede ser un voto positivo,
     * o un reporte negativo.
     * Redirecciona a la pagina del producto/juego del que habla
     *
     * @param integer $cId
     * @param boolean $esVotoPositivo
     * @return yii\web\Response
     */
    public function actionReportar($cId, $esVotoPositivo)
    {
        $reporte = ReportesCriticas::find()->where(['critica_id' => $cId, 'usuario_id' => Yii::$app->user->id])->one();

        if ($reporte) {
            if ($reporte->voto_positivo && $esVotoPositivo) {
                Yii::$app->session->setFlash('error', 'Ya le has dado megusta a esa crítica');
                return $this->redirect(['view', 'id' => $cId]);
            } elseif (!$reporte->voto_positivo && !$esVotoPositivo) {
                Yii::$app->session->setFlash('error', 'Ya has reportado esa critica');
                return $this->redirect(['view', 'id' => $cId]);
            } else {
                $reporte->voto_positivo = $esVotoPositivo;
            }
        } else {
            $reporte = new ReportesCriticas([
                'usuario_id' => Yii::$app->user->id,
                'critica_id' => $cId,
                'voto_positivo' => $esVotoPositivo,
            ]);
        }


        if ($reporte->save()) {
            if ($esVotoPositivo) {
                Yii::$app->session->setFlash('success', 'Has dado megusta a esta critica correctamente');

                // Hace critico al usuario que ha escrito la critica votada
                // si cumple las condiciones y si no lo es ya
                if (Usuarios::findOne($this->findModel($cId)->usuario_id)->cumpleRequisitoDeCritico() && !Usuarios::findOne($this->findModel($cId)->usuario_id)->es_critico) {
                    $usuario = Usuarios::findOne($this->findModel($cId)->usuario_id);
                    $usuario->es_critico = true;
                    $usuario->save();
                }
            } else {
                Yii::$app->session->setFlash('success', 'Has mandado un reporte correctamente');
            }
            return $this->redirect(['view', 'id' => $cId]);
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al procesar el reporte / megusta');
        return $this->redirect(['view', 'id' => $cId]);
    }

    /**
     * Crea una crítica de un producto
     * Esta accion está limitada solo a los usuarios poseedores del producto
     * que desean criticar.
     *
     * @param integer $producto_id
     * @return Response|string
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionCriticaProducto($producto_id)
    {
        $model = new Criticas();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['productos/view', 'id' => $producto_id]);
        }

        return $this->render('criticaProducto', [
            'model' => $model,
            'producto' => Productos::findOne($producto_id),
        ]);
    }

    /**
     * Crea una crítica de un juego.
     * Esta accion está limitada solo a los usuarios poseedores
     * del juego que desean criticar.
     *
     * @param integer $juego_id
     * @return Response|string
     */
    public function actionCriticaJuego($juego_id)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'No puedes hacer una reseña sin iniciar sesion!');
            return $this->redirect(['juegos/view', 'id' => $juego_id]);
        }

        if (Criticas::find()->where(['usuario_id' => Yii::$app->user->id, 'juego_id' => $juego_id])->one()) {
            Yii::$app->session->setFlash('error', '¡Ya has hecho una reseña de ese juego!');
            return $this->redirect(['juegos/view', 'id' => $juego_id]);
        }

        if (!Usuarios::findOne(Yii::$app->user->id)->tieneJuego($juego_id)) {
            Yii::$app->session->setFlash('error', 'No puedes hacer una reseña de un juego que no tienes');
            return $this->redirect(['juegos/view', 'id' => $juego_id]);
        }

        $model = new Criticas();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['juegos/view', 'id' => $juego_id]);
        }

        $model->juego_id = $juego_id;

        return $this->render('criticaJuego', [
            'model' => $model,
        ]);
    }

    /**
     * Encuentra el modelo Criticas basando el valor de su clave primaria.
     * Si el modelo no se encuentra, lanza una excepcion HTTP 404.
     * @param int $id
     * @return Criticas el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Criticas::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
}
