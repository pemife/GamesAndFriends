<?php

namespace app\controllers;

use app\models\Criticas;
use app\models\Etiquetas;
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
                'only' => ['create', 'update', 'delete', 'view'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->id == 1;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function ($rule, $action) {
                            $model = Juegos::findOne(Yii::$app->request->queryParams['id']);
                            if ($model->cont_adul == true) {
                                if (!Yii::$app->user->isGuest) {
                                    if (Usuarios::findOne(Yii::$app->user->id)->esMayorDeEdad()) {
                                        return true;
                                    }
                                    Yii::$app->session->setFlash('error', '¡Debes ser mayor de edad para ver este contenido!');
                                } else {
                                    Yii::$app->session->setFlash('error', '¡Debes iniciar sesión para ver contenido adulto!');
                                }
                                return false;
                            }

                            if (!Yii::$app->user->isGuest) {
                                $edadUsuarioSegundos = strtotime(date('Y-m-d')) - strtotime(Usuarios::findOne(Yii::$app->user->id)->fechanac);
                                $edadUsuario = $edadUsuarioSegundos / (60 * 60 * 24 * 365);
                                if ($model->edad_minima > $edadUsuario) {
                                    Yii::$app->session->setFlash('error', '¡Tu edad no cumple con los criterios para ver este juego!');
                                    return false;
                                }
                            }
                            
                            return true;
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
        // $searchModel->search(Yii::$app->request->queryParams);
        $searchModel = new JuegosSearch();

        $query = Juegos::find()->where(['cont_adul' => false]);

        if (!Yii::$app->user->isGuest) {
            $usuario = Usuarios::findOne(Yii::$app->user->id);
            if ($usuario->esMayorDeEdad()) {
                $query->orWhere(['cont_adul' => true]);
            }
            $query
            ->andWhere(['not in', 'id', $usuario->arrayIdJuegosIgnorados()]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

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
        ->select('precio')
        ->joinWith('copia')
        ->where(['juego_id' => $id])
        ->orderBy('precio')
        ->scalar();

        $criticasQuery = Criticas::find()
        ->joinWith('usuario')
        ->where(['juego_id' => $id]);

        $criticasProvider = new ActiveDataProvider([
            'query' => $criticasQuery,
            'pagination' => [
              'pagesize' => 10,
            ],
        ]);

        // Valoraciones positivas globales
        $criticasQuery = $criticasQuery
        ->andWhere(['>', 'valoracion', 3]);
        $valPosGlob = $criticasQuery->count();

        // Valoraciones positivas recientes
        $haceUnMes = date('Y-m-d', date('now') - strtotime('-1 month'));
        $valPosRec = $criticasQuery
        ->andWhere(['>', 'last_update', $haceUnMes])->count();

        $criticasProvider->sort->attributes['usuario.nombre'] = [
            'asc' => ['usuarios.nombre' => SORT_ASC],
            'desc' => ['usuarios.nombre' => SORT_DESC],
        ];

        $tieneJuego = Yii::$app->user->isGuest ? false : Usuarios::findOne(Yii::$app->user->id)->tieneJuego($id);

        //Juegos Similares
        $model = $this->findModel($id);
        $similaresProvider = new ActiveDataProvider([
            'query' => $model->similares(),
        ]);

        return $this->render('view', [
            'model' => $model,
            'precioMinimo' => $ventaMasBarata,
            'criticasProvider' => $criticasProvider,
            'tieneJuego' => $tieneJuego,
            'similaresProvider' => $similaresProvider,
            'valPosGlob' => $valPosGlob,
            'valPosRec' => $valPosRec,
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
            $juego = Yii::$app->request->post('Juegos');

            foreach ($juego['etiquetas'] as $idEtiqueta) {
                $model->link('etiquetas', Etiquetas::findOne($idEtiqueta));
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        foreach (Etiquetas::find()->all() as $etiqueta) {
            $generosArray[$etiqueta->id] = $etiqueta->nombre;
        }

        return $this->render('create', [
            'model' => $model,
            'generosArray' => $generosArray,
            'edadesValidas' => [3=>3,7=>7,12=>12,16=>16,18=>18],
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
            $juego = Yii::$app->request->post('Juegos');

            foreach ($juego['etiquetas'] as $idEtiqueta) {
                $model->link('etiquetas', Etiquetas::findOne($idEtiqueta));
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        foreach (Etiquetas::find()->all() as $etiqueta) {
            $generosArray[$etiqueta->id] = $etiqueta->nombre;
        }

        return $this->render('update', [
            'model' => $model,
            'generosArray' => $generosArray,
            'edadesValidas' => [3=>3,7=>7,12=>12,16=>16,18=>18],
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
        $queryJuegosNuevos = Juegos::find()->where(['cont_adul' => false])->orderBy('fechalan DESC')->limit(10)->offset(0);
        $queryRecomendaciones = Juegos::find()->where(['cont_adul' => false])->orderBy('fechalan DESC')->limit(10)->offset(0);

        if (!Yii::$app->user->isGuest) {
            $usuario = Usuarios::findOne(Yii::$app->user->identity->id);

            $queryRecomendaciones = Juegos::find()
            ->joinWith('etiquetas')
            ->where(['in', 'nombre', $usuario->generosPreferencia])
            ->andWhere(['cont_adul' => true]);

            if (!empty($usuario)) {
                if ($usuario->esMayorDeEdad()) {
                    $queryJuegosNuevos->orWhere(['cont_adul' => true]);
                    $queryRecomendaciones->orWhere(['cont_adul' => true]);
                }
                $queryJuegosNuevos
                ->andWhere(['not in', 'id', $usuario->arrayIdJuegosIgnorados()])
                ->andWhere(['<', 'fechalan', date('Y-m-d')]);
                $queryRecomendaciones
                ->andWhere(['not in', 'juegos.id', $usuario->arrayIdJuegosIgnorados()])
                ->andWhere(['<', 'fechalan', date('Y-m-d')]);
            }
        }

        $juegosProvider = new ActiveDataProvider([
            'query' => $queryJuegosNuevos,
            'pagination' => false,
        ]);

        $recomendacionesProvider = new ActiveDataProvider([
            'query' => $queryRecomendaciones,
        ]);

        return $this->render('novedades', [
            'juegosProvider' => $juegosProvider,
            'recomendacionesProvider' => $recomendacionesProvider,
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
