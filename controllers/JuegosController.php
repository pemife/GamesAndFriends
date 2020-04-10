<?php

namespace app\controllers;

use app\models\Criticas;
use app\models\Juegos;
use app\models\JuegosSearch;
use app\models\Usuarios;
use app\models\Ventas;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * JuegosController implements the CRUD actions for Juegos model.
 */
class JuegosController extends Controller
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
     * Lists all Juegos models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new JuegosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Juegos model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $ventaMasBarata = Ventas::find()
        ->joinWith('copia')
        ->where(['juego_id' => $id])
        ->orderBy('precio')
        ->one();

        $criticasQuery = Criticas::find()->where(['juego_id' => $id]);

        $criticasProvider = new ActiveDataProvider([
            'query' => $criticasQuery,
            'pagination' => [
              'pagesize' => 10,
            ],
        ]);

        $tieneJuego = Yii::$app->user->isGuest ? false : Usuarios::findOne(Yii::$app->user->id)->tieneJuego($id);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'precioMinimo' => $ventaMasBarata ? $ventaMasBarata->precio : null,
            'dataProvider' => $criticasProvider,
            'tieneJuego' => $tieneJuego,
        ]);
    }

    /**
     * Creates a new Juegos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Juegos();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Juegos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
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
     * Deletes an existing Juegos model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Muestra una vista con los juegos recien añadidos o actualizados.
     * @return mixed Renderiza una pagina con novedades de juegos
     */
    public function actionNovedades()
    {
        $searchModel = new JuegosSearch();
        $queryJuegosNuevos = Juegos::find()->orderBy('fechalan DESC')->limit(10)->offset(0);

        $juegosProvider = new ActiveDataProvider([
            'query' => $queryJuegosNuevos,
            'pagination' => false,
        ]);

        return $this->render('novedades', [
            'searchModel' => $searchModel,
            'juegosProvider' => $juegosProvider,
        ]);
    }

    /**
     * Finds the Juegos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Juegos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Juegos::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
