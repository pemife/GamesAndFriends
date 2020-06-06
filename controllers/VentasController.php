<?php

namespace app\controllers;

use app\models\Copias;
use app\models\Etiquetas;
use app\models\Juegos;
use app\models\Productos;
use app\models\Usuarios;
use app\models\Ventas;
use app\models\VentasSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * VentasController implementa las acciones CRUD para el modelo Ventas.
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
                'only' => [
                    'create', 'update', 'delete', 'mis-ventas', 'solicitar-compra',
                    'finalizar-venta', 'crea-venta-productos', 'crea-venta-item'
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡No puedes poner nada en venta sin loggear!');
                                return false;
                            }

                            if (Usuarios::findOne(Yii::$app->user->id)->esVerificado()) {
                                return true;
                            }

                            Yii::$app->session->setFlash('error', '¡No puedes poner nada en venta sin verificar tu cuenta!');
                            return false;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['mis-ventas'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'actions' => ['update'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            // Yii::debug(Yii::$app->request->queryParams['id']);
                            $model = Ventas::findOne(Yii::$app->request->queryParams['id']);
                            if (!Yii::$app->user->isGuest && ($model->vendedor_id == Yii::$app->user->id)) {
                                return true;
                            }
                            Yii::$app->session->setFlash('error', '¡No puedes modificar la venta de otra persona!');
                            return false;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['solicitar-compra'],
                        'matchCallback' => function ($rule, $action) {
                            $venta = Ventas::findOne(Yii::$app->request->queryParams['idVenta']);

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡No puedes solicitar nada sin iniciar sesion!');
                                return false;
                            }
                            
                            $comprador = Usuarios::findOne(Yii::$app->user->id);

                            if (!$comprador->esVerificado()) {
                                Yii::$app->session->setFlash('error', '¡No puedes crear la solicitud de compra sin verificar tu cuenta!');
                                return false;
                            }
                            
                            if (isset($comprador->solicitud)) {
                                Yii::$app->session->setFlash('error', '¡Ya tienes una compra solicitada!');
                                return false;
                            }
                            
                            if (Yii::$app->user->id == $venta->vendedor->id) {
                                Yii::$app->session->setFlash('error', '¡No puedes comprarte a ti mismo!');
                                return false;
                            }
                            
                            if (isset($venta->finished_at)) {
                                Yii::$app->session->setFlash('error', '¡No puedes solicitar la compra de una venta terminada!');
                                return false;
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['finalizar-venta'],
                        'matchCallback' => function ($rule, $action) {
                            $venta = Ventas::findOne(Yii::$app->request->queryParams['idVenta']);

                            if (isset($venta->finished_at)) {
                                Yii::$app->session->setFlash('error', '¡No puedes finalizar una venta que ya esta acabada!');
                                return false;
                            }

                            $comprador = Usuarios::findOne(Yii::$app->request->queryParams['idComprador']);

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡No puedes finalizar una venta sin iniciar sesion!');
                                return false;
                            }

                            if ($venta->vendedor->id == Yii::$app->user->id) {
                                if ($venta->vendedor->esVerificado() && $comprador->esVerificado()) {
                                    if ($comprador->solicitud->id == $venta->id) {
                                        return true;
                                    }
                                    Yii::$app->session->setFlash('error', '¡El comprador no ha solicitado esta venta!');
                                    return false;
                                }
                                Yii::$app->session->setFlash('error', '¡Los usuarios implicados en la venta deben estar verificados!');
                                return false;
                            }
                            Yii::$app->session->setFlash('error', '¡No puedes finalizar una venta que no es tuya!');
                            return false;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['crea-venta-productos', 'crea-venta-copias'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes vender algo sin iniciar sesion!');
                                return false;
                            }
                            
                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['crea-venta-item'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes vender algo sin iniciar sesion!');
                                return false;
                            }

                            $cId = Yii::$app->request->queryParams['cId'];
                            $pId = Yii::$app->request->queryParams['pId'];

                            if (empty($cId) && empty($pId)) {
                                Yii::$app->session->setFlash('error', 'No puedes vender un producto y una copia a la vez');
                                return false;
                            } elseif (!empty($cId) && !empty($pId)) {
                                Yii::$app->session->setFlash('error', 'No puedes crear una venta vacía');
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

        $usuario = Usuarios::findOne(Yii::$app->user->id);

        // Si el usuario es menor de edad, no muestra los juegos de contenido adulto
        if (!Yii::$app->user->isGuest) {
            if (!$usuario->esMayorDeEdad()) {
                $queryCopias->joinWith('copia.juego')->andWhere(['cont_adul' => false]);
            }

            $queryCopias->andWhere(['!=', 'vendedor_id', Yii::$app->user->id])
            ->andWhere(['not in', 'vendedor_id', $usuario->arrayUsuariosBloqueados(true)]);

            $queryProductos->andWhere(['!=', 'vendedor_id', Yii::$app->user->id])
            ->andWhere(['not in', 'vendedor_id', $usuario->arrayUsuariosBloqueados(true)]);
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
     * Displays a single Ventas.
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
     * Creates a new Ventas.
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
     * Actualiza un modelo Ventas.
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
     * Borra un modelo Ventas.
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
     * Muestra todas las ventas donde el vendedor es el usuario logueado.
     * @param  int  $u Id del usuario del que queremos ver sus ventas
     * @return string    Resultado de renderizado de la página
     */
    public function actionMisVentas($u)
    {
        $searchModel = new VentasSearch();

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
        $this->redirect(['/ventas/index']);
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
     * @param mixed $productoId
     */
    public function actionCreaVentaProductos($productoId)
    {
        $model = new Ventas();

        if ($productoId != 0) {
            $model->producto_id = $productoId;
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'Ha ocurrido un fallo al procesar tu venta');
        }

        if (Ventas::findOne(Yii::$app->request->queryParams['productoId'])) {
            Yii::$app->session->setFlash('error', 'Ese producto ya esta en venta');
            return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
        }

        // Crea un array asociativo con el id del producto a vender + el nombre
        $puedeVender = false;
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
    public function actionCreaVentaCopias()
    {
        $model = new Ventas();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'Ha ocurrido un fallo al procesar tu venta');
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

        return $this->render('creaVentaCopias', [
            'listaCopiasVenta' => $listaCopiasVenta,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    public function actionCreaVentaItem($cId, $pId)
    {
        $model = new Ventas();

        if ($model->load(Yii::$app->request->post())) {
            Yii::debug($model);
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            Yii::$app->session->setFlash('error', 'Ha ocurrido un fallo al procesar tu venta');
        }

        $nombreItem = empty($cId) ? Productos::findOne($pId)->nombre : Copias::findOne($cId)->juego->titulo;

        if (Ventas::find()->where(['producto_id' => $pId])->exists()) {
            Yii::$app->session->setFlash('error', 'Ese producto ya esta en venta');
            return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
        }

        if (Ventas::find()->where(['copia_id' => $cId])->andWhere(['finished_at' => null])->exists()) {
            Yii::$app->session->setFlash('error', 'Esa copia ya esta en venta');
            return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
        }

        if (Ventas::find()->where(['copia_id' => $cId])->andWhere(['<', 'finished_at', date('Y-m-d h:i:s')])->exists()) {
            Yii::$app->session->setFlash('error', 'La reventa de copias está bloqueada en esta página');
            return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
        }

        return $this->render('creaVentaItem', [
            'cId' => $cId,
            'pId' => $pId,
            'nombreItem' => $nombreItem,
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
        $query = Ventas::find()->where(['finished_at' => null]);

        if ($esProducto) {
            $query->joinWith('producto')
            ->andWhere(['producto_id' => $id]);

            $nombreItem = Productos::findOne($id)->nombre;
        } else {
            $query->joinWith('copia', 'juego')
            ->andWhere(['juego_id' => $id]);

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

    public function actionSolicitarCompra($idVenta)
    {
        $venta = Ventas::findOne($idVenta);
        $vendedor = Usuarios::findOne($venta->vendedor_id);
        $comprador = Usuarios::findOne(Yii::$app->user->id);
        $comprador->venta_solicitada = $idVenta;

        $mail = Yii::$app->mailer->compose(
            'confirmacionVenta',
            [
                'idVenta' => $venta->id,
                'idComprador' => Yii::$app->user->id,
            ]
        )
        ->setFrom('gamesandfriends2@gmail.com')
        ->setTo($vendedor->email)
        ->setSubject('Confirmacion de venta');

        if ($comprador->save()) {
            if ($mail->send()) {
                Yii::$app->session->setFlash('info', '¡Se ha enviado la solicitud!');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Ha ocurrido un error en la solicitud de compra');
        }

        return $this->redirect(['ventas/view', 'id' => $venta->id]);
    }

    public function actionFinalizarVenta($idVenta, $idComprador)
    {
        $venta = Ventas::findOne($idVenta);
        $comprador = Usuarios::findOne($idComprador);

        $item = $venta->producto ? Productos::findOne($venta->producto->id) : Copias::findOne($venta->copia->id);

        $item->propietario_id = $comprador->id;
        $venta->comprador_id = $comprador->id;
        $comprador->venta_solicitada = null;
        $venta->finished_at = date('Y-m-d H:i:s');

        if ($item->validate() && $venta->validate() && $comprador->validate()) {
            // Aqui se realizara el pago, una vez que la aplicacion soporte paypal

            $item->save();
            $venta->save();
            $comprador->save();

            Yii::$app->session->setFlash('success', 'Venta finalizada con exito');

            $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
        } else {
            Yii::$app->session->setFlash('error', 'Ha ocurrido un error procesando la venta');
        }
        Yii::debug($item, $venta, $comprador);
        return false;
    }

    /**
     * Finds the Ventas model based on its primary key value.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     * @param int $id
     * @return Ventas el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Ventas::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
}
