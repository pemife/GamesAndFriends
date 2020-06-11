<?php

namespace app\controllers;

use app\models\Copias;
use app\models\CopiasSearch;
use app\models\Juegos;
use app\models\Plataformas;
use app\models\Precios;
use app\models\Usuarios;
use app\models\Ventas;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;

/**
 * CopiasController implementa las acciones CRUD para el modelo Copias.
 */
class CopiasController extends Controller
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
                'only' => ['create', 'update', 'delete', 'mis-copias', 'comprar-copia', 'retirar-inventario'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'mis-copias'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            $model = Copias::findOne(Yii::$app->request->queryParams['id']);

                            // Yii::$app->session->setFlash('error', '');
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes modificar/borrar nada sin iniciar sesion');
                                return false;
                            }

                            if ($model->propietario_id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', '¡No puedes modificar/borrar la copia de otra persona!');
                                return false;
                            }

                            if ($model->estado == 'En venta') {
                                Yii::$app->session->setFlash('error', 'No puedes modificar/borrar una copia que esta en venta');
                                return false;
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['comprar-copia'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes comprar nada sin iniciar sesion');
                                return false;
                            }

                            $usuario = Usuarios::findOne(Yii::$app->user->id);

                            if (!$usuario->esVerificado()) {
                                Yii::$app->session->setFlash('error', '¡Debes verificar tu cuenta antes de comprar un juego!');
                                return false;
                            }

                            if (!Juegos::findOne(Yii::$app->request->queryParams['jId'])) {
                                Yii::$app->session->setFlash('error', '¡Ese juego no existe!');
                                return false;
                            }

                            if (!Plataformas::findOne(Yii::$app->request->queryParams['pId'])) {
                                Yii::$app->session->setFlash('error', '¡Intentas comprar un juego para una plataforma que no existe!');
                                return false;
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['retirar-inventario'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes retirar nada sin iniciar sesion');
                                return false;
                            }

                            $copia = Copias::find()
                            ->where(['id' => Yii::$app->request->queryParams['id']])
                            ->one();

                            if (!$copia) {
                                Yii::$app->session->setFlash('error', 'No puedes retirar de tu inventario una copia que no existe!');
                                return false;
                            }

                            if ($copia->propietario_id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes retirar una copia del inventario de otra persona');
                                return false;
                            }

                            $model = Copias::findOne(Yii::$app->request->queryParams['id']);
                            if ($model->estado == 'En venta') {
                                Yii::$app->session->setFlash('error', 'No puedes retirar una copia que esta en venta');
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
     * Lista de todos los modelos Copias.
     * Si el usuario esta logueado, muestra solo sus copias.
     *
     * @return Response|string la pagina renderizada
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            $this->redirect(['copias/mis-copias', 'id' => Yii::$app->user->id]);
        }

        $query = Copias::find()
        ->joinWith('juego')
        ->orderBy('titulo');

        $searchModel = new CopiasSearch();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
              'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Muestra un único modelo de Copias.
     *
     * @param int $id
     * @return Response|string
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionView($id)
    {
        if (Yii::$app->user->id == $this->findModel($id)->propietario_id) {
            return $this->render('view', [
              'model' => $this->findModel($id),
            ]);
        }
        Yii::$app->session->setFlash('error', '¡No tienes acceso a esa copia!');
        return $this->goBack();
    }

    /**
     * Crea un modelo nuevo de Copias.
     * Si la creacion es exitosa, el navegador redireccionara a la pagina de vista del modelo.
     * Esta acción esta limitada a los usuarios logueados, servirá para que los usuarios
     * puedan añadir a su inventario una clave de juego que quieran vender.
     *
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new Copias();

        $model->scenario = Copias::SCENARIO_ANADIR_INVENTARIO;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'listaJuegos' => Juegos::listaAsociativa(),
            'listaPlataformas' => Plataformas::listaAsociativa(),
        ]);
    }

    /**
     * Actualiza un modelo existente de Copias.
     * Si se actualiza correctamente, el navegador será redireccionado a la pagina de vista.
     * Esta accion está limitada solo al usuario propietario de la copia, y solo podrá modificar
     * dicha copia si no se encuentra en venta en el momento.
     *
     * @param int $id
     * @return Response|string
     * @throws NotFoundHttpException si el modelo no se encuentra
     * @throws ForbiddenHttpException si no supera las reglas de acceso
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
     * Borra un modelo existente de Copias.
     * Si se borra exitosamente, sera redireccionado a copias/index.
     * Esta accion está limitada solo al usuario propietario de la copia, y solo podrá borrar
     * dicha copia si no se encuentra en venta en el momento.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException si el modelo no se encuentra.
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Función que retira la copia de la propiedad del usuario
     * Esta accion está limitada solo al usuario propietario de la copia, y solo podrá retirar
     * dicha copia si no se encuentra en venta en el momento.
     *
     * @param integer $id el ID de la copia de la que se retirara la propiedad
     * @return Response
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionRetirarInventario($id)
    {
        $this->findModel($id)->unlink('propietario', Usuarios::findOne(Yii::$app->user->id));

        Yii::$app->session->setFlash('success', 'Copia retirada de inventario correctamente');

        return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
    }

    /**
     * Devuelve un índice de las copias del usuario logueado
     *
     * @param integer $id el id del usuario
     * @return string la pagina renderizada
     */
    public function actionMisCopias($id)
    {
        $usuario = Usuarios::findOne($id);

        $query = Copias::find()
        ->where(['propietario_id' => $id])
        ->joinWith('juego')
        ->orderBy('titulo');

        $searchModel = new CopiasSearch();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
              'pageSize' => 20,
            ],
        ]);

        return $this->render('misCopias', [
            'modelUsuario' => $usuario,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Procesa todas las copias almacenadas en el carrito y
     * muestra una vista para proceder al pago
     *
     * Si da un error, muestra que copia ha dado el error y redirecciona
     * a la pagina de inicio.
     *
     * @return Response
     */
    public function actionProcesarCarrito()
    {
        if (!Yii::$app->request->cookies->has('Carro-' . Yii::$app->user->id)) {
            Yii::$app->session->setFlash('error', 'No tienes nada en el carrito');
            return $this->redirect(['home']);
        }

        $cookieCarro = Yii::$app->request->cookies->getValue('Carro-' . Yii::$app->user->id);

        $precios = explode(' ', $cookieCarro);

        $copias = [];

        foreach ($precios as $precioId) {
            $precio = Precios::findOne($precioId);

            $copia = new Copias([
                'juego_id' => $precio->juego_id,
                'plataforma_id' => $precio->plataforma_id,
                'propietario_id' => Yii::$app->user->id
            ]);

            $copias[] = $copia;

            // Valido las copias antes de la transacción
            if (!$copia->validate()) {
                Yii::$app->session->setFlash('error', '¡Ha ocurrido un error al procesar la compra [Copia inválida]!');
                return $this->redirect(['home']);
            }
        }

        foreach ($copias as $copia) {
            if (!$copia->save()) {
                Yii::$app->session->setFlash('error', 'Ha ocurrido un error al añadir copias a tu inventario');
                return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
            }
        }

        $cookie = new Cookie([
            'name' => 'Carro-' . Yii::$app->user->id,
            'value' =>  '',
            'expire' => time() + 86400 * 365,
            'secure' => true,
        ]);

        Yii::$app->response->cookies->add($cookie);
        
        Yii::$app->session->setFlash('success', 'Se ha realizado la compra correctamente!');
        return $this->redirect(['usuarios/view', 'id' => Yii::$app->user->id]);
    }

    /**
     * Encuentra el modelo Copias basado el la clave primaria.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     *
     * @param int $id
     * @return Copias el modelo buscado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Copias::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
}
