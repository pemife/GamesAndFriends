<?php

namespace app\controllers;

use app\models\Criticas;
use app\models\Productos;
use app\models\ReportesCriticas;
use app\models\Usuarios;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CriticasController implements the CRUD actions for Criticas model.
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
                'only' => ['create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
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
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->goBack();
    }

    /**
     * Displays a single Criticas model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->redirect(['productos/view', 'id' => Criticas::findOne($id)->producto_id]);
    }

    /**
     * Creates a new Criticas model.
     * If creation is successful, the browser will be redirected to the 'view' page.
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
     * Updates an existing Criticas model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->esCriticaProducto()) {
                return $this->redirect(['productos/view', 'id' => $model->producto_id]);
            }
            return $this->redirect(['juegos/view', 'id' => $model->juego_id]);
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
     * Deletes an existing Criticas model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
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

    public function actionReportar($cId)
    {
        $reporte = ReportesCriticas::find()->where(['critica_id' => $cId, 'usuario_id' => Yii::$app->user->id])->exists();
        $url = Url::to(
            $this->findModel($cId)->juego ? 'juegos/view' : 'productos/view',
            ['id' => $this->findModel($cId)->juego ? $this->findModel($cId)->juego->id : $this->findModel($cId)->producto->id]
        );

        if ($reporte) {
            Yii::$app->session->setFlash('error', 'Ya has reportado esa critica');
            return $this->redirect($url);
        }

        $reporte = new ReportesCriticas([
            'usuario_id' => Yii::$app->user->id,
            'critica_id' => $cId,
        ]);

        if ($reporte->save()) {
            Yii::$app->session->setFlash('success', 'Has mandado un reporte correctamente');
            return $this->redirect($url);
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al procesar el reporte');
        return $this->redirect($url);
    }

    public function actionCriticaProducto($producto_id)
    {
        $model = new Criticas();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['productos/view', 'id' => $producto_id]);
        }

        // $model->producto_id = $producto->id;

        return $this->render('criticaProducto', [
            'model' => $model,
            'producto' => Productos::findOne($producto_id),
        ]);
    }

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
     * Finds the Criticas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Criticas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Criticas::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
