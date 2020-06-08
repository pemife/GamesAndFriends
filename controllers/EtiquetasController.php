<?php

namespace app\controllers;

use app\models\Etiquetas;
use app\models\EtiquetasSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * EtiquetasController implementa las acciones CRUD para el modelo Etiquetas.
 */
class EtiquetasController extends Controller
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
                        'actions' => ['create', 'update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->id == 1;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Lista todos los modelos de Etiquetas.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EtiquetasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Muestra un único modelo Etiquetas.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Crea un nuevo modelo Etiquetas.
     * Si la creacion es exitosa, el navegador redirecciona a la pagina de vista de la etiqueta.
     * Esta accion está limitada solo al usuario administrador.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Etiquetas();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualiza un modelo existente de Etiquetas.
     * Si la actualizacion es exitosa, redirecciona a la pagina de vista de la etiqueta.
     * Esta accion está limitada solo al usuario administrador.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     * @throws ForbiddenHttpException si el usuario logueado no es admin
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Borra un modelo existente de Etiquetas.
     * Si el borrado es exitoso, redirecciona a la pagina de 'indice' de etiquetas.
     * Esta accion está limitada solo al usuario administrador.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     * @throws ForbiddenHttpException si el usuario logueado no es admin
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Encuentra el modelo Etiquetas basado en el valor de su clave primaria.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     * @param int $id
     * @return Etiquetas el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Etiquetas::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
}
