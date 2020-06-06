<?php

namespace app\controllers;

use app\models\Comentarios;
use app\models\ComentariosSearch;
use app\models\ReportesComentarios;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ComentariosController implementa las acciones CRUD para el modelo de Comentarios.
 */
class ComentariosController extends Controller
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
                            $model = Comentarios::findOne(Yii::$app->request->queryParams['id']);
                            if (!Yii::$app->user->isGuest && ($model->usuario_id == Yii::$app->user->id)) {
                                return true;
                            }
                            Yii::$app->session->setFlash('error', '¡No puedes modificar el comentario de otra persona!');
                            return false;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Crea un modelo nuevo de Comentarios
     * Si se crea correctamente, el navegador sera redireccionado al post comentado.
     * Solo pueden acceder a esta accion, los usuarios logueados.
     * @return mixed
     */
    public function actionCreate($pId)
    {
        $model = new Comentarios();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['posts/view', 'id' => $pId]);
        }

        return $this->render('create', [
            'model' => $model,
            'pId' => $pId,
        ]);
    }

    /**
     * Actualiza un modelo existente de Comentarios.
     * Si se crea correctamente, el navegador sera redireccionado al post comentado.
     * Solo puede acceder a esta acción el usuario creador del post.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no existe
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['posts/view', 'id' => $model->post->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Borra un modelo de Comentarios existente
     * Si se crea correctamente, el navegador sera redireccionado al
     * post donde estaba el comentario.
     * Solo puede acceder a esta acción el usuario creador del post.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionDelete($id)
    {
        $post = $this->findModel($id)->post->id;
        $this->findModel($id)->delete();

        return $this->redirect(['posts/view', 'id' => $post]);
    }

    /**
     * Accion que crea un reporte para un comentario
     * Redirecciona a la pagina del post donde se encuentra el comentario reportado
     *
     * @param integer $cId el ID del comentario a reportar
     * @return mixed la pagina renderizada
     */
    public function actionReportar($cId)
    {
        $reporte = ReportesComentarios::find()->where(['comentario_id' => $cId, 'usuario_id' => Yii::$app->user->id])->exists();
        $post = $this->findModel($cId)->post->id;

        if ($reporte) {
            Yii::$app->session->setFlash('error', 'Ya has reportado ese comentario');
            return $this->redirect(['posts/view', 'id' => $post]);
        }

        $reporte = new ReportesComentarios([
            'usuario_id' => Yii::$app->user->id,
            'comentario_id' => $cId,
        ]);

        if ($reporte->save()) {
            Yii::$app->session->setFlash('success', 'Has mandado un reporte correctamente');
            return $this->redirect(['posts/view', 'id' => $post]);
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al procesar el reporte');
        return $this->redirect(['posts/view', 'id' => $post]);
    }

    /**
     * Encuentra el modelo Comentarios basado en la clave primaria.
     * Si el modelo no se encuentra, una excepcion HTTP 404 será lanzada
     * @param int $id
     * @return Comentarios el modelo encontrado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Comentarios::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
}
