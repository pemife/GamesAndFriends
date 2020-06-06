<?php

namespace app\controllers;

use app\models\Criticas;
use app\models\Etiquetas;
use app\models\Juegos;
use app\models\JuegosSearch;
use app\models\Plataformas;
use app\models\Precios;
use app\models\Usuarios;
use app\models\Ventas;
use Yii;
use yii\bootstrap4\Html;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;

/**
 * JuegosController implementa las acciones CRUD para el modelo Juegos.
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
                'only' => ['create', 'update', 'delete', 'view', 'anadir-carrito'],
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
                            $model = $this->findModel(Yii::$app->request->queryParams['id']);
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
                    [
                        'allow' => true,
                        'actions' => ['anadir-carrito'],
                        'matchCallback' => function ($rule, $action) {

                            if (!Precios::findOne(Yii::$app->request->queryParams['pId'])) {
                                Yii::$app->session->setFlash('error', '¡No hay opcion de compra disponible para ese juego!');
                                return false;
                            }
                            
                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['poner-oferta'],
                        'matchCallback' => function ($rule, $action) {

                            if (!Juegos::findOne(Yii::$app->request->queryParams['jId'])) {
                                Yii::$app->session->setFlash('error', '¡Ese juego no existe!');
                                return false;
                            }
                            
                            return true;
                        },
                    ],
                ],
            ],
          ];
    }

    /**
     * Lista todos los modelos
     *
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
     * Muestra un único modelo Juegos.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

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
     * Crea un nuevo modelo Juegos.
     * Si la creación es exitosa, redirecciona a la pagina de vista del juego creado.
     * Esta accion está limitada solo al usuario administrador.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Juegos();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $juego = Yii::$app->request->post('Juegos');

            if ($juego['etiquetas']) {
                foreach ($juego['etiquetas'] as $idEtiqueta) {
                    $model->link('etiquetas', Etiquetas::findOne($idEtiqueta));
                }
            }

            if ($juego['plataformas']) {
                foreach ($juego['plataformas'] as $idPlataforma) {
                    $model->link('plataformas', Plataformas::findOne($idPlataforma));
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        foreach (Etiquetas::find()->all() as $etiqueta) {
            $generosArray[$etiqueta->id] = $etiqueta->nombre;
        }

        foreach (Plataformas::find()->all() as $plataforma) {
            $plataformasArray[$plataforma->id] = $plataforma->nombre;
        }

        return $this->render('create', [
            'model' => $model,
            'generosArray' => $generosArray,
            'plataformasArray' => $plataformasArray,
            'edadesValidas' => [3=>3,7=>7,12=>12,16=>16,18=>18],
        ]);
    }

    /**
     * Actualiza un modelo Juegos.
     * Si se actualiza con éxito, redireciona a la pagina de vista del modelo.
     * Esta accion está limitada solo al usuario administrador.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $juego = Yii::$app->request->post('Juegos');

            if ($juego['etiquetas']) {
                foreach ($model->etiquetas as $etiqueta) {
                    $model->unlink('etiquetas', $etiqueta, true);
                }
                foreach ($juego['etiquetas'] as $idEtiqueta) {
                    $model->link('etiquetas', Etiquetas::findOne($idEtiqueta));
                }
            }

            if ($juego['plataformas']) {
                foreach ($model->precios as $precio) {
                    $model->unlink('precios', $precio, true);
                }
                foreach ($juego['plataformas'] as $idPlataforma) {
                    $model->link('plataformas', Plataformas::findOne($idPlataforma));
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        foreach (Etiquetas::find()->all() as $etiqueta) {
            $generosArray[$etiqueta->id] = $etiqueta->nombre;
        }

        foreach (Plataformas::find()->all() as $plataforma) {
            $plataformasArray[$plataforma->id] = $plataforma->nombre;
        }

        return $this->render('update', [
            'model' => $model,
            'generosArray' => $generosArray,
            'plataformasArray' => $plataformasArray,
            'edadesValidas' => [3=>3,7=>7,12=>12,16=>16,18=>18],
        ]);
    }

    /**
     * Borra un modelo Juegos.
     * Si el borrado es exitoso, redirecciona a la pagina indice
     * Esta accion está limitada solo al usuario administrador.
     *
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
     * Muestra una vista con los juegos recien añadidos o actualizados.
     *
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
            ->where(['in', 'etiquetas.id', $usuario->generosPreferencia(true)]);
            
            if (!empty($usuario)) {
                if (!$usuario->esMayorDeEdad()) {
                    $queryJuegosNuevos->andWhere(['cont_adul' => false]);
                    $queryRecomendaciones->andWhere(['cont_adul' => false]);
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
     * Crea o actualiza la cookie de carrito de compra, que almacena
     * los juegos que se quiere comprar las plataformas en las
     * que se desea comprar, para el posterior procesamiento de compra.
     *
     * @param integer $pId el ID de la plataforma
     * @return mixed si se ha añadido, devuelve la cookie, sino, false
     */
    public function actionAnadirCarrito($pId)
    {
        if (!Yii::$app->request->cookies->has('Carro-' . Yii::$app->user->id)) {
            $cookie = new Cookie([
                'name' => 'Carro-' . Yii::$app->user->id,
                'value' => $pId,
                'expire' => time() + 86400 * 365,
                'secure' => true,
            ]);

            Yii::$app->response->cookies->add($cookie);

            return Yii::$app->request->cookies->getValue('Carro-' . Yii::$app->user->id);
        } else {
            $cookieAntes = Yii::$app->request->cookies->getValue('Carro-' . Yii::$app->user->id);

            $cookie = new Cookie([
                'name' => 'Carro-' . Yii::$app->user->id,
                'value' =>  $cookieAntes . ' ' . $pId,
                'expire' => time() + 86400 * 365,
                'secure' => true,
            ]);

            Yii::$app->response->cookies->add($cookie);

            return Yii::$app->request->cookies->getValue('Carro-' . Yii::$app->user->id);
        }

        return false;
    }

    /**
     * Muestra una vista con los juegos añadidos anteriormente al carrito
     * para procesar su compra.
     * Si no tiene nada en el carro, redirecciona a la pagina de inicio.
     *
     * @return string la vista 'carritoCompra'
     */
    public function actionCarritoCompra()
    {
        if (!Yii::$app->request->cookies->has('Carro-' . Yii::$app->user->id)) {
            Yii::$app->session->setFlash('error', 'No tienes nada en el carrito');
            return $this->redirect(['site/index']);
        }

        $cookieCarro = Yii::$app->request->cookies->getValue('Carro-' . Yii::$app->user->id);

        $precios = explode(' ', $cookieCarro);

        $query = Precios::find()->where(['IN', 'id', $precios]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $precioTotal = 0;

        foreach ($dataProvider->getModels() as $precio) {
            $precioTotal += $precio->cifra;
        }

        return $this->render('carritoCompra', [
            'dataProvider' => $dataProvider,
            'precioTotal' => $precioTotal,
        ]);
    }

    public function actionPonerOferta($jId, $porcentaje)
    {
        $precios = Precios::find()->where(['juego_id' => $jId])->all();

        foreach ($precios as $precio) {
            $precio->oferta = $porcentaje;
            Yii::debug($precio);
            if (!$precio->save()) {
                Yii::$app->session->setFlash('error', 'Ha ocurrido un error al poner la oferta');
                return $this->redirect(['juegos/view', 'id' => $jId]);
            }
        }

        if ($this->enviaCorreoRecomendaciones($jId)) {
            Yii::$app->session->setFlash('success', 'Se han enviado todos los correos');
        }

        Yii::$app->session->setFlash('success', 'Oferta asignada correctamente');
        return $this->redirect(['juegos/view', 'id' => $jId]);
    }

    /**
     * Finds the Juegos model based on its primary key value.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     * @param int $id
     * @return Juegos el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Juegos::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }

    private function enviaCorreoRecomendaciones($jId)
    {
        $emailsusuariosRecomendaciones = Usuarios::find()
        ->joinWith('deseados')
        ->joinWith('deseados.juego')
        ->where(['juegos.id' => $jId])
        ->select('usuarios.email')
        ->distinct()
        ->column();

        if (!$emailsusuariosRecomendaciones) {
            Yii::$app->session->setFlash('success', 'No se ha enviado ningun correo');
            return false;
        }

        $emailsFallados = [];
        
        foreach ($emailsusuariosRecomendaciones as $email) {
            $correo = Yii::$app->mailer->compose()
            ->setFrom('gamesandfriends2@gmail.com')
            ->setTo($email)
            ->setSubject('¡Un juego en tu lista de deseos esta en oferta!')
            ->setHtmlBody(
                '¡El juego '
                . Html::a(
                    $this->findModel($jId)->titulo,
                    Url::to(['juegos/view', 'id' => $jId], true)
                )
                . ' está de oferta!'
            );

            if (!$correo->send()) {
                $emailsFallados[] = $email;
            }
        }
        
        if ($emailsFallados) {
            Yii::$app->session->setFlash('error', 'Ha fallado el envio de correos de estas direcciones' . implode(', ', $emailsFallados));
            return false;
        }

        return true;
    }
}
