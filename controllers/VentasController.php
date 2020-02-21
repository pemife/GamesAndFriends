<?php

namespace app\controllers;

use app\models\Copias;
use app\models\Etiquetas;
use app\models\Juegos;
use app\models\Productos;
use app\models\Ventas;
use app\models\VentasSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * VentasController implements the CRUD actions for Ventas model.
 */
class VentasController extends Controller
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
                'only' => ['create', 'update', 'delete', 'mis-ventas'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete', 'mis-ventas'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Muestra todas las ventas.
     * @return mixed
     */
    public function actionIndex()
    {
        // La query perfecta seria:
        // select disticnt on (p.nombre) nombre, v.precio from ventas v join
        // productos p on v.producto_id=p.id order by p.nombre, v.precio;
        // Para enseñar de cada juego, la venta mas barata
        $searchModel = new VentasSearch();

        // Si el usuario no esta logueado, se le muestran todas las copias/productos
        // en venta
        // Una query para copias y otra para productos
        $queryCopias = Ventas::find()
        ->where([
            'finished_at' => null,
            'producto_id' => null,
        ]);

        $queryProductos = Ventas::find()
        ->where([
            'finished_at' => null,
            'copia_id' => null,
        ]);

        if (!Yii::$app->user->isGuest) {
            $queryCopias->andWhere(['!=', 'vendedor_id', Yii::$app->user->id]);

            $queryProductos->andWhere(['!=', 'vendedor_id', Yii::$app->user->id]);
        }

        $copiasProvider = new ActiveDataProvider([
            'query' => $queryCopias,
            'pagination' => [
              'pageSize' => 5,
            ],
        ]);

        $productosProvider = new ActiveDataProvider([
            'query' => $queryProductos,
            'pagination' => [
              'pageSize' => 5,
            ],
        ]);

        $generos = Etiquetas::find()->orderBy('nombre')->all();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'copiasProvider' => $copiasProvider,
            'productosProvider' => $productosProvider,
            'generos' => $generos,
        ]);
    }

    /**
     * Displays a single Ventas model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Ventas model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Ventas();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->producto_id === '0') {
                $model->producto_id = null;
            }
            if ($model->copia_id === '0') {
                $model->copia_id = null;
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'No puedes vender algo sin iniciar sesion!');
            return $this->redirect(['ventas/index']);
        }

        // Inserto el primer valor que saldra por defecto
        $listaProductosVenta['0'] = null;
        $listaCopiasVenta['0'] = null;
        $puedeVender = false;

        // Crea un array asociativo con el id del producto a vender + el nombre
        foreach (Productos::lista() as $producto) {
            $listaProductosVenta[$producto->id] = $producto->nombre;
            $puedeVender = true;
        }

        foreach (Copias::listaQuery()->all() as $copia) {
            $listaCopiasVenta[$copia->id] = $copia->juego->titulo;
            $puedeVender = true;
        }

        if (!$puedeVender) {
            Yii::$app->session->setFlash('error', 'Tu usuario no posee ningun producto o copia!');
            return $this->redirect(['ventas/index']);
        }

        return $this->render('create', [
            'listaProductosVenta' => $listaProductosVenta,
            'listaCopiasVenta' => $listaCopiasVenta,
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Ventas model.
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

        if ($model->producto === null) {
            $listaCopiasVenta['0'] = null;
            foreach (Copias::listaQuery()->all() as $copia) {
                $listaCopiasVenta[$copia->id] = $copia->juego->titulo;
            }
            return $this->render('updateCopia', [
                'listaCopiasVenta' => $listaCopiasVenta,
                'model' => $model,
            ]);
        } elseif ($model->copia === null) {
            $listaProductosVenta['0'] = null;
            foreach (Productos::lista() as $producto) {
                $listaProductosVenta[$producto->id] = $producto->nombre;
            }
            return $this->render('updateProducto', [
                'listaProductosVenta' => $listaProductosVenta,
                'model' => $model,
            ]);
        }

        Yii::$app->session->setFlash('error', 'No se ha actualizado la puesta en venta');
        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing Ventas model.
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
     * Muestra todas las ventas donde el vendedor es el usuario logueado.
     * @param  int  $u Id del usuario del que queremos ver sus ventas
     * @return string    Resultado de renderizado de la página
     */
    public function actionMisVentas($u)
    {
        $searchModel = new VentasSearch();
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', '¡No has iniciado sesión!');
            return $this->redirect(['index']);
        }

        if (Yii::$app->user->id == $u) {
            $queryMisProductos = Ventas::find()->where([
                'finished_at' => null,
                'copia_id' => null,
                'vendedor_id' => Yii::$app->user->id,
            ]);

            $queryMisCopias = Ventas::find()->where([
                'finished_at' => null,
                'producto_id' => null,
                'vendedor_id' => Yii::$app->user->id,
            ]);

            $misProductosProvider = new ActiveDataProvider([
                'query' => $queryMisProductos,
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);

            $misCopiasProvider = new ActiveDataProvider([
                'query' => $queryMisCopias,
                'pagination' => [
                    'pageSize' => 5,
                ],
            ]);

            if ($misProductosProvider->count == 0 && $misCopiasProvider->count == 0) {
                Yii::$app->session->setFlash('error', 'No tienes ningun producto o copia en venta!');
            }

            return $this->render('misVentas', [
              'misProductosProvider' => $misProductosProvider,
              'misCopiasProvider' => $misCopiasProvider,
            ]);
        }

        Yii::$app->session->setFlash('error', 'No puedes acceder a las ventas de otra persona!');
        $this->goBack();
    }

    /**
     * Esta accion filtra las copias por nombre y por genero [TODO].
     * @param  string $nombre El nombre del juego de Copia
     * @param  string $genero El genero del juego de Copia
     * @return [type]         [description]
     */
    public function actionFiltraCopias($nombre, $genero)
    {
        $dataProvider = new ActiveDataProvider([
          'query' => Ventas::find()
          ->where(['finished_at' => null])
          ->filterWhere([]),
        ]);
    }

    /**
     * Esta accion sirve para la creacion de la venta de un producto.
     * @return string El resultado del renderizado de la página
     */
    public function actionCreaVentaProducto()
    {
        $model = new Ventas();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'Ha ocurrido un fallo al procesar tu venta');
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'No puedes vender algo sin iniciar sesion!');
            return $this->redirect(['ventas/index']);
        }

        // Crea un array asociativo con el id del producto a vender + el nombre
        foreach (Productos::lista() as $producto) {
            $listaProductosVenta[$producto->id] = $producto->nombre;
            $puedeVender = true;
        }

        if (!$puedeVender) {
            Yii::$app->session->setFlash('error', '¡Tu usuario no posee ningun producto!');
            return $this->redirect(['ventas/index']);
        }

        return $this->render('creaVentaProducto', [
            'listaProductosVenta' => $listaProductosVenta,
            'model' => $model,
        ]);
    }

    /**
     * Esta accion sirve para la creacion de la venta de una copia.
     * @return string El resultado del renderizado de la página
     */
    public function actionCreaVentaCopia()
    {
        $model = new Ventas();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'Ha ocurrido un fallo al procesar tu venta');
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'No puedes vender algo sin iniciar sesion!');
            return $this->redirect(['ventas/index']);
        }

        // Crea un array asociativo con el id de la copia a vender + el nombre
        foreach (Copias::listaQuery()->all() as $copia) {
            $listaCopiasVenta[$copia->id] = $copia->juego->titulo;
        }

        $dataProvider = new ActiveDataProvider([
          'query' => Copias::listaQuery(),
        ]);

        if ($dataProvider->count == 0) {
            Yii::$app->session->setFlash('error', '¡Tu usuario no posee ninguna copia!');
            return $this->redirect(['ventas/index']);
        }

        return $this->render('creaVentaCopia', [
            'listaCopiasVenta' => $listaCopiasVenta,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * Accion que renderiza una lista de todas las ventas
     * de un item concreto.
     * @param mixed $id El id del item que queremos usar
     * @param bool $esProducto Boolean para saber si es un producto o una copia
     * @return string     El resultado del renderizado
     */
    public function actionVentasItem($id, $esProducto)
    {
        $query = Ventas::find();

        if ($esProducto) {
            $query->joinWith('producto')
            ->where(['producto_id' => $id]);

            $nombreItem = Productos::findOne($id)->nombre;
        } else {
            $query->joinWith('copia')
            ->where(['copia_id' => $id]);

            $nombreItem = Juegos::findOne($id)->titulo;
        }

        $query->orderBy('precio');

        $ventasProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('ventasItem', [
            'esProducto' => $esProducto,
            'nombreItem' => $nombreItem,
            'ventasProvider' => $ventasProvider,
        ]);
    }

    /**
     * Accion que renderiza una lista de todas las ventas
     * de un producto concreto.
     * @param  int $id El id de un producto
     * @return string     El resultado del renderizado
     */
    public function actionVentasProducto($id)
    {
        $query = Ventas::find()
        ->joinWith('producto')
        ->where(['producto_id' => $id])
        ->orderBy('precio');

        $ventasProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('ventasItem', [
            'ventasProvider' => $ventasProvider,
        ]);
    }

    /**
     * Finds the Ventas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Ventas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ventas::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
