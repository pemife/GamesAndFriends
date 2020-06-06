<?php

namespace app\controllers;

use app\models\Criticas;
use app\models\Productos;
use app\models\ProductosSearch;
use app\models\Usuarios;
use app\models\Ventas;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ProductosController implementa las acciones CRUD para el modelo Productos.
 */
class ProductosController extends Controller
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
                'only' => ['create', 'update', 'delete', 'index'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            $model = Productos::findOne(Yii::$app->request->queryParams['id']);

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes modificar/borrar nada sin iniciar sesion');
                                return false;
                            }

                            if ($model->propietario_id == Yii::$app->user->id) {
                                return true;
                            }

                            if ($model->estado == 'En venta') {
                                Yii::$app->session->setFlash('error', 'No puedes modificar/borrar un producto que esta en venta');
                                return false;
                            }

                            Yii::$app->session->setFlash('error', '¡No puedes modificar el producto de otra persona!');
                            return false;
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Productos models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductosSearch();

        $query = Productos::find();

        if (!Yii::$app->user->isGuest) {
            $query->where(['!=', 'propietario_id', Yii::$app->user->id])
            ->andWhere(['not in', 'propietario_id', Usuarios::findOne(Yii::$app->user->id)->arrayUsuariosBloqueados(true)]);
        }

        $dataProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => ['pagesize' => 20],
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Productos.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionView($id)
    {
        $criticasQuery = Criticas::find()->joinWith('usuario')->where(['producto_id' => $id]);

        $criticasProvider = new ActiveDataProvider([
            'query' => $criticasQuery,
            'pagination' => [
              'pagesize' => 10,
            ],
        ]);

        $criticasProvider->sort->attributes['usuario.nombre'] = [
            'asc' => ['usuarios.nombre' => SORT_ASC],
            'desc' => ['usuarios.nombre' => SORT_DESC],
        ];

        $tieneProducto = Yii::$app->user->isGuest ? false : Usuarios::findOne(Yii::$app->user->id)->tieneProducto($id);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider' => $criticasProvider,
            'tieneProducto' => $tieneProducto,
        ]);
    }

    /**
     * Creates a new Productos.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Productos();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualiza un modelo Productos.
     * Si se actualiza con éxito, redireciona a la pagina de vista del modelo.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
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
     * Borra un modelo Productos.
     * Si el borrado es exitoso, redirecciona a la pagina indice
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Productos model based on its primary key value.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     * @param int $id
     * @return Productos el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Productos::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
}
