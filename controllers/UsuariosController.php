<?php

namespace app\controllers;

use app\models\Copias;
use app\models\Productos;
use app\models\Usuarios;
use app\models\UsuariosSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * UsuariosController implements the CRUD actions for Usuarios model.
 */
class UsuariosController extends Controller
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
                'class' => AccessControl::classname(),
                'only' => ['update', 'login', 'logout'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            $model = Usuarios::findOne(Yii::$app->request->queryParams['id']);
                            if (!Yii::$app->user->isGuest && ($model->id == Yii::$app->user->id)) {
                                return true;
                            }
                            Yii::$app->session->setFlash('error', '¡No puedes modificar el perfil de otra persona!');
                            return false;
                        },
                    ],
                  ],
            ],
        ];
    }

    /**
     * Lists all Usuarios models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsuariosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Usuarios model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $queryProductos = Productos::find()
        ->where(['propietario_id' => $id]);

        $queryCopias = Copias::find()
        ->where(['propietario_id' => $id]);

        $productosProvider = new ActiveDataProvider([
          'query' => $queryProductos,
          'pagination' => ['pageSize' => 5],
        ]);

        $copiasProvider = new ActiveDataProvider([
          'query' => $queryCopias,
          'pagination' => ['pageSize' => 5],
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'productosProvider' => $productosProvider,
            'copiasProvider' => $copiasProvider,
        ]);
    }

    /**
     * Creates a new Usuarios model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Usuarios();

        $model->scenario = Usuarios::SCENARIO_CREATE;

        if ($model->load(Yii::$app->request->post())) {
            $model->token = $model->creaToken();
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Usuarios model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->tienePermisos($model)) {
            $model->scenario = Usuarios::SCENARIO_UPDATE;

            if ($model->load(Yii::$app->request->post())) {
                // var_dump($model);
                // exit;
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }

            $model->password = '';

            return $this->render('update', [
              'model' => $model,
            ]);
        }
        Yii::$app->session->setFlash('danger', 'No puedes modificar el perfil de otra persona');
        return $this->goHome();
    }

    /**
     * Deletes an existing Usuarios model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($this->tienePermisos($model)) {
            $model->delete();

            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('danger', 'No puedes borrar el perfil de otra persona');
        return $this->goHome();
    }

    public function actionCambioPass($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->user->id !== $model->id) {
            Yii::$app->session->setFlash('error', 'Validación incorrecta de usuario');
            return $this->redirect(['site/login']);
        }
        $model->scenario = Usuarios::SCENARIO_CAMBIOPASS;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('info', 'La contraseña se ha guardado correctamente');
            return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
        }
        $model->password = $model->password_repeat = '';
        return $this->render('cambioPass', [
            'model' => $model,
        ]);
    }

    // https://jqueryui.com/sortable/
    // public fucntion actionListaDeseos($uId)
    // {
    //     return null;
    // }

    /**
     * Finds the Usuarios model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Usuarios the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Usuarios::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function tienePermisos($model)
    {
        return Yii::$app->user->id === 1 || Yii::$app->user->id === $model->id;
    }
}
