<?php

namespace app\controllers;

use app\models\Copias;
use app\models\Deseados;
use app\models\Etiquetas;
use app\models\Ignorados;
use app\models\Juegos;
use app\models\LoginForm;
use app\models\Productos;
use app\models\Relaciones;
use app\models\Usuarios;
use Aws\S3\S3Client;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\bootstrap4\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * UsuariosController implementa las acciones CRUD para el modelo Usuarios.
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
                'only' => [
                    'view', 'create', 'update', 'delete',
                    'login', 'logout', 'mandar-peticion',
                    'bloquear-usuario', 'anadir-amigo',
                    'desbloquear-usuario', 'ver-lista-deseos',
                    'index', 'ordenar-lista-deseos',
                    'seguir-critico', 'abandonar-critico',
                    'index-filtrado', 'lista-seguidos', 'anadir-preferencias'
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'create'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'index', 'lista-seguidos', 'anadir-preferencias'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('danger', 'Tienes que iniciar sesion para ver el perfil de un usuario');
                                return false;
                            }
                            
                            $model = $this->findModel(Yii::$app->request->queryParams['id']);
                            
                            if ($model->id == Yii::$app->user->id) {
                                return true;
                            }

                            if ($model->estadoRelacion(Yii::$app->user->id) == 3) {
                                Yii::$app->session->setFlash('error', '¡Este perfil esta bloqueado!');
                                return false;
                            }

                            if (!$model->esAmigo(Yii::$app->user->id)) {
                                Yii::$app->session->setFlash('error', 'No puedes ver el perfil de un usuario que no sea tu amigo');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡No puedes modificar perfiles sin iniciar sesión!');
                                return false;
                            }

                            if (Yii::$app->user->id === 1) {
                                return true;
                            }
                            
                            $model = $this->findModel(Yii::$app->request->queryParams['id']);
                            if ($model->id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', '¡No puedes modificar el perfil de otra persona!');
                                return false;
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['mandar-peticion'],
                        'matchCallback' => function ($rule, $action) {
                            
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para añadir amigos');
                                return false;
                            }
                        
                            if (!$this->findModel(Yii::$app->user->id)->esVerificado()) {
                                Yii::$app->session->setFlash('error', 'Tienes que verificar tu cuenta para añadir amigos');
                                return false;
                            }

                            switch ($this->findModel(Yii::$app->user->id)->estadoRelacion(Yii::$app->request->queryParams['amigoId'])) {
                                case 0:
                                    Yii::$app->session->setFlash('danger', 'Ya teneis una peticion de amistad pendiente');
                                    return false;
                                case 1:
                                    Yii::$app->session->setFlash('danger', '¡Ya sois amigos!');
                                    return false;
                                case 2:
                                    return true;
                                case 3:
                                    Yii::$app->session->setFlash('error', 'No puedes enviar una peticion de amistad a este usuario');
                                    return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['anadir-amigo'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para aceptar peticiones de amistad');
                                return false;
                            }

                            $amigoId = Yii::$app->request->queryParams['amigoId'];
                            
                            if (Yii::$app->user->id != $amigoId) {
                                Yii::$app->session->setFlash('error', 'No puedes aceptar esta peticion de amistad');
                                return false;
                            }
                            
                            $usuarioId = Yii::$app->request->queryParams['usuarioId'];
                            $usuario = $this->findModel($usuarioId);

                            switch ($usuario->estadoRelacion($amigoId)) {
                                case 1:
                                    Yii::$app->session->setFlash('error', '¡Ya sois amigos!');
                                    return false;
                                break;
                                case 3:
                                    Yii::$app->session->setFlash('error', 'No puedes ser amigo de este usuario [Bloqueado]');
                                    return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['bloquear-usuario'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para bloquear usuarios');
                                return false;
                            }

                            $usuario = $this->findModel(Yii::$app->request->queryParams['usuarioId']);

                            if ($usuario->estaBloqueadoPor(Yii::$app->user->id)) {
                                Yii::$app->session->setFlash('error', 'Ya has bloqueado a ese usuario');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['desbloquear-usuario'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para desbloquear usuarios');
                                return false;
                            }

                            $usuario = $this->findModel(Yii::$app->request->queryParams['usuarioId']);

                            if (!$usuario->estaBloqueadoPor(Yii::$app->user->id)) {
                                Yii::$app->session->setFlash('error', '¡A ese usuario no lo tenías bloqueado!');
                                return false;
                            }

                            if ($this->findModel(Yii::$app->user->id)->relacionCon($usuario->id)->usuario1_id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes desbloquear a este usuario');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['ver-lista-deseos'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para ver lista de deseos');
                                return false;
                            }

                            if (Yii::$app->request->queryParams['uId'] == Yii::$app->user->id) {
                                return true;
                            }

                            $usuario = $this->findModel(Yii::$app->request->queryParams['uId']);

                            if ($usuario->estaBloqueadoPor(Yii::$app->user->id) || !$usuario->esAmigo(Yii::$app->user->id)) {
                                Yii::$app->session->setFlash('error', '¡No puedes ver la lista de deseos de este usuario!');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['anadir-deseos'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para añadir a lista de deseos');
                                return false;
                            }

                            if (Yii::$app->request->queryParams['uId'] != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes añadir un juego a una lista de deseos que sea la tuya');
                                return false;
                            }

                            if (!Juegos::findOne(Yii::$app->request->queryParams['jId'])) {
                                Yii::$app->session->setFlash('error', 'No puedes añadir a la lista de deseos un juego que no existe');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['borrar-deseos'],
                        'matchCallback' => function ($rule, $action) {
                            
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para añadir a lista de deseos');
                                return false;
                            }

                            $uId = Yii::$app->request->queryParams['uId'];

                            if (Yii::$app->user->id != $uId) {
                                Yii::$app->session->setFlash('error', '¡No puedes borrar un juego de la lista de deseos de otra persona!');
                                return false;
                            }

                            $jId = Yii::$app->request->queryParams['jId'];
                            
                            if (!Juegos::findOne($jId)) {
                                Yii::$app->session->setFlash('error', '¡No puedes borrar de la lista un juego que no existe!');
                                return false;
                            }
                            
                            $deseo = Deseados::find()
                            ->where(['usuario_id' => $uId, 'juego_id' => $jId])
                            ->one();

                            if (!$deseo) {
                                Yii::$app->session->setFlash('error', '¡Ese juego no esta en tu lista de desos!');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['ordenar-lista-deseos'],
                        'matchCallback' => function ($rule, $action) {
                            
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para ordenar la lista de deseos');
                                return $this->redirect(['site/login']);
                            }

                            $uId = Yii::$app->request->post()['uId'];

                            if (Yii::$app->user->id != $uId) {
                                Yii::$app->session->setFlash('error', '¡No puedes ordenar la lista de deseos de otra persona!');
                                return $this->redirect(['site/home']);
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['anadir-ignorados', 'borrar-ignorados'],
                        'matchCallback' => function ($rule, $action) {
                            $palabraAccion = explode('-', $action->id)[0];

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para ignorar un juego');
                                return false;
                            }

                            if (Yii::$app->request->queryParams['uId'] != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes ' . $palabraAccion . ' un juego en la lista de ignorados de otra persona');
                                return false;
                            }

                            if (!Juegos::findOne(Yii::$app->request->queryParams['jId'])) {
                                Yii::$app->session->setFlash('error', 'No puedes ' . $palabraAccion . ' en la lista de ignorados un juego que no existe');
                                return false;
                            }

                            $uId = Yii::$app->request->queryParams['uId'];

                            $jId = Yii::$app->request->queryParams['jId'];
                            
                            $ignorado = Ignorados::find()
                            ->where(['usuario_id' => $uId, 'juego_id' => $jId])
                            ->exists();

                            if (!$ignorado) {
                                if ($palabraAccion == 'borrar') {
                                    Yii::$app->session->setFlash('error', '¡Ese juego no lo has ignorado!');
                                    return false;
                                }
                            } else {
                                if ($palabraAccion == 'anadir') {
                                    Yii::$app->session->setFlash('error', '¡Ese juego ya lo has ignorado!');
                                    return false;
                                }
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['ver-lista-ignorados'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para ver lista de ignorados');
                                return false;
                            }

                            if (Yii::$app->request->queryParams['uId'] != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', '¡No puedes ver la lista de ignorados de otra persona!');
                                return false;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['seguir-critico', 'abandonar-critico'],
                        'matchCallback' => function ($rule, $action) {
                            $palabraAccion = explode('-', $action->id)[0];

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para ' . $palabraAccion . ' a un crítico');
                                return false;
                            }

                            $usuarioCritico = $this->findModel(Yii::$app->request->queryParams['uId']);

                            if (!$usuarioCritico->es_critico) {
                                Yii::$app->session->setFlash('error', 'Ese usuario no es crítico');
                                return false;
                            }

                            if ($usuarioCritico->estadoRelacion(Yii::$app->user->id) == 3) {
                                Yii::$app->session->setFlash('error', 'No puedes seguir a ese crítico [Bloqueado]');
                                return false;
                            }

                            switch ($palabraAccion) {
                                case 'seguir':
                                    if ($usuarioCritico->estaSeguidoPor(Yii::$app->user->id)) {
                                        Yii::$app->session->setFlash('error', '¡Ya sigues a ese crítico!');
                                        return false;
                                    }
                                break;
                                case 'abandonar':
                                    if (!$usuarioCritico->estaSeguidoPor(Yii::$app->user->id)) {
                                        Yii::$app->session->setFlash('error', '¡No sigues a ese crítico!');
                                        return false;
                                    }
                                break;
                            }

                            return true;
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index-filtrado'],
                        'matchCallback' => function ($rule, $action) {
                            
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'Debes iniciar sesión para ver lista de usuarios');
                                return false;
                            }
                            
                            // No muestra la lista de seguidores si el usuario no es crítico de juegos
                            $tipoLista = Yii::$app->request->queryParams['tipoLista'];
                            if ($tipoLista == 'seguidores') {
                                if (!$this->findModel(Yii::$app->user->id)->es_critico) {
                                    Yii::$app->session->setFlash('error', 'Debes ser crítico para tener seguidores');
                                    return false;
                                }
                            }

                            return true;
                        }
                    ],
                  ],
            ],
        ];
    }

    /**
     * Lista todos los modelos de usuario
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $usuario = $this->findModel(Yii::$app->user->id);

        $IdsUsuariosBloqueados = $usuario->arrayUsuariosBloqueados(true);
        if ($IdsUsuariosBloqueados) {
            $query = Usuarios::find()
            ->where(['not in', 'id', $usuario->arrayUsuariosBloqueados(true)]);
        } else {
            $query = Usuarios::find();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tipoLista' => 'normal'
        ]);
    }

    /**
     * Muestra un único modelo Usuarios.
     * Esta accion esta limitada a usuarios logueados que sean amigos del usuario
     * del que se quiere ver el perfil o el propio usuario del perfil.
     * El acceso a usuarios no amigos/bloqueados, esta bloqueado tambien.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     * @throws ForbiddenHttpException el usuario que solicite la accion debe ser amigo.
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

        $copiasUsuario = $copiasProvider->getModels();

        $juegosUsuarioIds = [0];

        foreach ($copiasUsuario as $copia) {
            $juegosUsuarioIds[] = $copia->juego_id;
        }

        $usuariosRecomendadosQuery = Usuarios::find()
        ->joinWith('copias')
        ->joinWith('copias.juego')
        ->where(['IN', 'juegos.id', $juegosUsuarioIds])
        ->andWhere(['not', ['usuarios.id' => $id]])
        ->select('usuarios.nombre, usuarios.id')
        ->distinct();

        $usuariosRecomendadosProvider = new ActiveDataProvider([
            'query' => $usuariosRecomendadosQuery
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'usuariosRecomendadosProvider' => $usuariosRecomendadosProvider,
            'productosProvider' => $productosProvider,
            'copiasProvider' => $copiasProvider,
        ]);
    }

    /**
     * Crea un nuevo modelo Usuarios.
     * Es la accion que permite registrarse a los usuarios nuevos a la pagina.
     * Esta accion esta limitada a los usuarios no logueados.
     * Si la creacion es exitosa, redirecciona a la pagina de vista.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Usuarios();

        $model->scenario = Usuarios::SCENARIO_CREATE;

        if ($model->load(Yii::$app->request->post())) {
            $model->token = $model->creaToken();
            if ($model->save()) {
                $this->enviaCorreoBienvenida($model->id);

                $usuario = Yii::$app->request->post('Usuarios');

                $modelLogin = new LoginForm([
                    'username' => $model->nombre,
                    'password' => $usuario['password'],
                    'rememberMe' => '1',
                ]);

                $modelLogin->login();

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Actualiza un modelo Usuarios.
     * Si se actualiza con éxito, redireciona a la pagina de vista del modelo.
     * Esta acción esta limitada a el usuario cuyo perfil se quiere editar,
     * o al usuario administrador.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     * @throws ForbiddenHttpException si el usuario logueado no es el del perfil que se quiere modificar.
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            $model->scenario = Usuarios::SCENARIO_UPDATE;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $model->password = '';

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Borra un modelo Usuarios.
     * Si el borrado es exitoso, redirecciona a la pagina indice.
     * Esta acción esta limitada a el usuario cuyo perfil se
     * quiere borrar, o al usuario administrador.
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra.
     * @throws ForbiddenHttpException si el usuario logueado no es el del perfil que se quiere borrar.
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $model->delete();
            
        return $this->goHome();
    }

    /**
     * Funcion que permite el cambio de contraseña de usuario.
     * Esta acción está limitada al usuario cuya contraseña se quiere cambiar.
     *
     * @param integer $id el ID del usuario a cambiar
     * @return yii\web\Response|string
     */
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

    /**
     * Muestra una vista donde puede elegir si quiere añadir un juego o un producto
     * a su inventario, para su posterior venta.
     *
     * @return string
     */
    public function actionAnadirInventario()
    {
        return $this->render('anadirInventario');
    }

    /**
     * Permite solicitar la verificación del correo electronico, enviando
     * un enlace de verificación
     *
     * @return Response
     */
    public function actionSolicitarVerificacion()
    {
        if (!Yii::$app->user->isGuest) {
            $usuario = $this->findModel(Yii::$app->user->id);
            $usuario->requested_at = (new \DateTime('now', new \DateTimeZone('Europe/Madrid')))->format('Y-m-d H:i:s');

            $usuario->scenario = Usuarios::SCENARIO_VERIFICACION;
            if ($usuario->save()) {
                $this->enviaCorreoConfirmacion(Yii::$app->user->id);
            } else {
                Yii::$app->session->setFlash('error', 'Error al solicitar la verificacion');
                Yii::debug($usuario);
            }

            return $this->redirect(['view', 'id' => Yii::$app->user->id]);
        }

        Yii::$app->session->setFlash('error', 'Debes iniciar sesion para solicitar la verificacion de tu cuenta');
        return $this->redirect(['site/login']);
    }

    /**
     * Acción que verifica la dirección de correo del usuario
     *
     * @return Response
     */
    public function actionVerificar($token)
    {

        if (!Yii::$app->user->isGuest) {
            $usuario = $this->findModel(Yii::$app->user->id);

            $aTiempo = (time() - strtotime($usuario->requested_at)) < 3600;

            if ($usuario->token === $token && $aTiempo) {
                $usuario->token = null;

                $usuario->scenario = Usuarios::SCENARIO_VERIFICACION;
                Yii::debug($usuario);
                if ($usuario->save()) {
                    Yii::$app->session->setFlash('success', 'Tu cuenta ha sido verificada');
                    return $this->redirect(['site/index']);
                }
                Yii::debug([$usuario, $token, $aTiempo]);
                Yii::$app->session->setFlash('error', 'Ha ocurrido un error guardando los cambios');
                return $this->redirect(['view', 'id' => $usuario->id]);
            }
            Yii::$app->session->setFlash('error', 'Ha ocurrido un error al verificar la cuenta (intentalo de nuevo)');

            return $this->redirect(['site/index']);
        }

        Yii::$app->session->setFlash('error', 'Debes iniciar session para verificar tu cuenta');
        return $this->redirect(['site/login']);
    }

    /**
     * Devuelve la renderización en AJAX de una tabla con los
     * usuarios que sean amigos del usuario cuyo ID se ha pasado
     * como parametro de la funcion.
     *
     * @param integer $usuarioId el id del usuario del que queremos ver los amigos
     * @return string la vista renderizada
     */
    public function actionListaAmigos($usuarioId)
    {
        $usuario = $this->findModel($usuarioId);

        return $this->renderAjax('vistaAmigos', [
          'listaAmigos' => $usuario->arrayRelacionados(1),
        ]);
    }

    /**
     * Añade a otro usuario a una relacion con el usuario con
     * la sesion iniciada, y los relaciona como amigos.
     * Esta acción está limitada al usuario al que fué mandada
     * la peticion de amistad.
     * (siempre que este usuario no esté bloqueado por el primer usuario).
     *
     * @param integer $usuarioId el id del usuario 1
     * @param integer $amigoId el id del usuario 2
     * @return Response
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionAnadirAmigo($usuarioId, $amigoId)
    {
        $usuario = $this->findModel($usuarioId);

        if ($usuario->esAmigo($amigoId)) {
            Yii::$app->session->setFlash('error', 'Ya sois amigos!');
            return $this->redirect(['view', 'id' => $usuarioId]);
        }
        
        $relacion = Relaciones::find()
        ->where(['usuario1_id' => $usuarioId, 'usuario2_id' => $amigoId])
        ->orWhere(['usuario1_id' => $amigoId, 'usuario2_id' => $usuarioId])
        ->one();

        $relacion->old_estado = $relacion->estado;
        $relacion->estado = 1;

        if ($relacion->save()) {
            Yii::$app->session->setFlash('success', '¡Te has añadido satisfactoriamente como amigo!');
            return $this->redirect(['view', 'id' => $usuarioId]);
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al añadirse como amigo');
        return $this->redirect(['view', 'id' => $usuarioId]);
    }

    /**
     * Manda una peticion de amistad por correo al usuario obetivo
     * que podrá aceptar.
     * Al mandar la peticion, se crea una relacion entre los usuarios,
     * una relacion de amistad pendiente de aceptar.
     *
     * @param integer $amigoId el id del usuario al que manda la peticion
     * @return Response
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionMandarPeticion($amigoId)
    {
        if ($this->enviaPeticionAmistad($amigoId)) {
            $relacion = new Relaciones([
                'usuario1_id' => Yii::$app->user->id,
                'usuario2_id' => $amigoId,
                'estado' => 0,
            ]);
            if ($relacion->save()) {
                Yii::$app->session->setFlash('success', 'Petición de amistad guardada');
                return $this->redirect(['index']);
            }
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Borra la relacion entre dos usuarios que eran amigos
     *
     * @param integer $amigoId el id del usuario amigo
     * @return void
     */
    public function actionBorrarAmigo($amigoId)
    {
        $usuario = $this->findModel(Yii::$app->user->id);

        if (!$usuario->esAmigo($amigoId)) {
            Yii::$app->session->setFlash('error', 'No sois amigos!');
            return $this->redirect(['index']);
        }
        
        $relacion = Relaciones::find()
        ->where(['estado' => 1, 'usuario1_id' => $usuario->id, 'usuario2_id' => $amigoId])
        ->orWhere(['usuario1_id' => $amigoId, 'usuario2_id' => $usuario->id])
        ->one();

        $relacion->delete();

        if ($usuario->esAmigo($amigoId)) {
            Yii::$app->session->setFlash('error', 'Ha ocurrido un error al borrarse como amigo');
            return $this->redirect(['view', ['id' => $amigoId]]);
        }

        Yii::$app->session->setFlash('info', 'Has borrado al usuario ' . $usuario->nombre . ' de tu lista de amigos');
        return $this->redirect(['index']);
    }

    /**
     * Crea un bloqueo entre usuarios, lo que negara varios tipos de
     * contenido entre los usuarios que esten bloqueados.
     *
     * @param integer $usuarioId el usuario a bloquear
     * @return void
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionBloquearUsuario($usuarioId)
    {
        $usuario = $this->findModel($usuarioId);

        $relaciones = $usuario->relacionesCon(Yii::$app->user->id);
        
        if (empty($relaciones)) {
            $relacion = new Relaciones([
                'usuario1_id' => Yii::$app->user->id,
                'usuario2_id' => $usuario->id,
                'estado' => 3,
            ]);
        } else {
            if (sizeof($relaciones) == 1) {
                $relacion = $relaciones[0];

                if ($relacion->usuario1_id == Yii::$app->user->id) {
                    $relacion->old_estado = $relacion->estado;
                    $relacion->estado = 3;
                } else {
                    $relacion = new Relaciones([
                        'usuario1_id' => Yii::$app->user->id,
                        'usuario2_id' => $usuario->id,
                        'estado' => 3,
                    ]);
                }
            } else {
                foreach ($relaciones as $relacion) {
                    if ($relacion->estado == 1) {
                        $relacion->delete();
                        Yii::$app->session->setFlash('info', 'Has actualizado tu relación a "bloqueado"');
                        return $this->redirect(['index']);
                    }
                }
            }
        }

        if ($relacion->save()) {
            Yii::$app->session->setFlash('info', 'Has bloqueado satisfactoriamente a ' . $usuario->nombre);
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al bloquear al usuario ' . $usuario->nombre);
        return $this->redirect(['index']);
    }

    /**
     * Permite desbloquear a usuarios entre sí
     *
     * @param integer $usuarioId el usuario bloqueado anteriormente
     * @return void
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionDesbloquearUsuario($usuarioId)
    {
        $usuario = $this->findModel(Yii::$app->user->id);

        $relacion = $usuario->relacionCon($usuarioId);

        $relacion->delete();
        Yii::$app->session->setFlash('success', 'Has desbloqueado satisfactoriamente este perfil');
        return $this->redirect(['index']);
    }

    /**
     * Renderiza por una peticion AJAX, una vista con los usuarios bloqueados.
     *
     * @param integer $usuarioId
     * @return string
     */
    public function actionListaBloqueados($usuarioId)
    {
        $usuario = $this->findModel($usuarioId);

        return $this->renderAjax('vistaBloqueados', [
          'listaBloqueados' => $usuario->listaBloqueados(),
        ]);
    }

    /**
     * Muestra una vista con todos los juegos que desea un usuario
     * Esta acción está limitada a los usuarios relacionados
     * a la lista de deseos y a sus amigos.
     *
     * @param integer $uId
     * @return string
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionVerListaDeseos($uId)
    {
        $deseadosProvider = new ActiveDataProvider([
            'query' => $this->findModel($uId)->getDeseados()->orderBy('orden'),
        ]);

        return $this->render('listaDeseos', [
            'deseadosProvider' => $deseadosProvider,
            'usuario' => $this->findModel($uId),
        ]);
    }

    /**
     * Añade un juego a la lista de deseos de un usuario
     *
     * @param integer $uId  el id del usuario
     * @param integer $jId  el id del juego
     * @return string|Response
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionAnadirDeseos($uId, $jId)
    {
        $repetido = Deseados::find()
        ->where(['usuario_id' => $uId, 'juego_id' => $jId])
        ->exists();

        if ($repetido) {
            if (Yii::$app->request->isAjax) {
                return Json::encode('¡Ese juego ya esta en tu lista de deseados!');
            }
            Yii::$app->session->setFlash('error', '¡Ese juego ya esta en tu lista de deseados!');
            return $this->redirect(['ver-lista-deseos', 'uId' => $uId]);
        }

        $deseo = new Deseados([
            'usuario_id' => $uId,
            'juego_id' => $jId,
        ]);

        if ($deseo->save()) {
            if (Yii::$app->request->isAjax) {
                $this->actualizarOrdenDeseados($uId);
                return Json::encode('¡Has añadido el juego satisfactoriamente!');
            }
            $this->actualizarOrdenDeseados($uId);
            Yii::$app->session->setFlash('success', '¡Has añadido el juego satisfactoriamente!');
            return $this->redirect(['ver-lista-deseos', 'uId' => $uId]);
        }
        
        if (Yii::$app->request->isAjax) {
            return Json::encode('¡Ha ocurrido un error al añadir el juego!');
        }

        Yii::$app->session->setFlash('error', '¡Ha ocurrido un error al añadir el juego!');
        return $this->redirect(['ver-lista-deseos', 'uId' => $uId]);
    }

    /**
     * Borra un juego de la lista de deseos
     *
     * @param integer $uId el id del usuario
     * @param integer $jIdel id del juego
     * @return void
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionBorrarDeseos($uId, $jId)
    {
        $deseo = Deseados::find()
        ->where(['usuario_id' => $uId, 'juego_id' => $jId])
        ->one();

        $deseo->delete();

        $this->actualizarOrdenDeseados($uId);
        
        return $this->redirect(['ver-lista-deseos', 'uId' => $uId]);
    }

    /**
     * Ordena la lista de deseos con un nuevo orden
     *
     * @return boolean si se ha ordenado correctamente, (true) o ha ocurrido un error (false)
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionOrdenarListaDeseos()
    {
        $post = Yii::$app->request->post();
        $uId = $post['uId'];
        $nO = $post['nO'];

        Yii::debug($nO);

        $deseados = Deseados::find()
        ->where(['usuario_id' => $uId])
        ->orderBy('orden')
        ->all();

        if (!$deseados || (count($deseados) != (count($nO)))) {
            Yii::debug('el usuario no tiene deseos o los arrays no coinciden');
            return false;
        }
        Yii::debug('todo va bien');

        for ($i = 0; $i < count($deseados); $i++) {
            for ($j = 0; $j < count($nO); $j++) {
                if ($deseados[$i]->juego->id == $nO[$j]) {
                    $deseo = $deseados[$i];
                    $deseo->orden = $j+1;
                    if (!$deseo->save()) {
                        Yii::$app->session->setFlash('error', 'Ha ocurrido un error actualizando el orden de la lista de deseos');
                        return $this->redirect(['ver-lista-deseos', 'uId' => $uId]);
                    }
                }
            }
        }

        return true;
    }
    
    /**
     * Accion que renderiza la lista de juegos que el usuario ha ignorado
     *
     * @param integer $uId
     * @return string
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionVerListaIgnorados($uId)
    {
        $ignoradosProvider = new ArrayDataProvider([
            'allModels' => Ignorados::find()
            ->where(['usuario_id' => $uId])
            ->all(),
        ]);

        return $this->render('listaIgnorados', [
            'ignoradosProvider' => $ignoradosProvider,
            'usuario' => $this->findModel($uId),
        ]);
    }

    /**
     * Añade un juego a la lista de juegos ignorados del usuario.
     *
     * @param integer $uId el id del usuario de la lista
     * @param integer $jId el id del juego que se quiere añadir
     * @return string
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionAnadirIgnorados($uId, $jId)
    {
        $repetido = Ignorados::find()
        ->where(['usuario_id' => $uId, 'juego_id' => $jId])
        ->exists();

        if ($repetido) {
            if (Yii::$app->request->isAjax) {
                return Json::encode('¡Ese juego ya lo has ignorado!');
            }
            Yii::$app->session->setFlash('error', '¡Ese juego ya lo has ignorado!');
            return $this->redirect(['juegos/index']);
        }

        $ignorado = new Ignorados([
            'usuario_id' => $uId,
            'juego_id' => $jId,
        ]);

        if ($ignorado->save()) {
            if (Yii::$app->request->isAjax) {
                return Json::encode('¡Has ignorado el juego satisfactoriamente!');
            }
            Yii::$app->session->setFlash('success', '¡Has ignorado el juego satisfactoriamente!');
            return $this->redirect(['juegos/index']);
        }
        
        if (Yii::$app->request->isAjax) {
            return Json::encode('¡Ha ocurrido un error al ignorar el juego!');
        }

        Yii::$app->session->setFlash('error', '¡Ha ocurrido un error al ignorar el juego!');
        return $this->redirect(['juegos/index']);
    }

    /**
     * Borra un juego de la lista de bloqueados del usuario
     *
     * @param integer $uId el id del usuario
     * @param integer $jId el id del juego
     * @return Response
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionBorrarIgnorados($uId, $jId)
    {
        $ignorado = Ignorados::find()
        ->where(['usuario_id' => $uId, 'juego_id' => $jId])
        ->one();

        $ignorado->delete();
        
        return $this->redirect(['juegos/index']);
    }

    /**
     * Devuelde una vista con la lista de un tipo de usuarios.
     * Puede ser de usuarios bloqueados por el usuario,
     * de usuarios normales, críticos, de seguidores, o de usuarios
     * a los que sigue el usuario.
     * Tambien muestra la lista filtrada por un texto insertado
     * como parámetro.
     *
     * @param string $texto string que filtrará la lista
     * @param integer $tipoLista
     * @return string
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionIndexFiltrado($texto, $tipoLista)
    {
        $usuario = $this->findModel(Yii::$app->user->id);

        $IdsUsuariosBloqueados = $usuario->arrayUsuariosBloqueados(true);
        $query = Usuarios::find();
        switch ($tipoLista) {
            case 'bloqueados':
                $query->where(['in', 'id', $usuario->listaIdsBloqueados()]);
            break;
            case 'criticos':
                $query->where(['es_critico' => true])
                ->andWhere(['not in', 'id', $IdsUsuariosBloqueados]);
            break;
            case 'seguidores':
                $query->where(['in', 'id', $usuario->listaSeguidoresId()]);
            break;
            case 'seguidos':
                $query->where(['in', 'id', $usuario->listaCriticosSeguidosId()]);
            break;
            default:
                $query->where(['not in', 'id', $IdsUsuariosBloqueados]);
        }

        if ($texto) {
            if (filter_var($texto, FILTER_VALIDATE_EMAIL)) {
                $query->andWhere(['ilike', 'email', $texto]);
            } else {
                $query->andWhere(['ilike', 'nombre', $texto]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (Yii::$app->request->isAjax) {
            return $this->renderPartial('gridUsuarios', [
                'dataProvider' => $dataProvider,
                'tipoLista' => $tipoLista,
            ]);
        }
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tipoLista' => $tipoLista
        ]);
    }

    /**
     * Crea una relación de seguimiento entre el usuario del modelo y el
     * usuario pasado por parametros como ID.
     *
     * @param integer $uId el id del usuario a seguir
     * @return Response|string
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionSeguirCritico($uId)
    {
        $usuario = $this->findModel(Yii::$app->user->id);

        if ($usuario->esAmigo($uId)) {
            $relacion = $usuario->relacionCon($uId);
            $relacion->delete();
        }

        $relacion = new Relaciones([
            'usuario1_id' => Yii::$app->user->id,
            'usuario2_id' => $uId,
            'estado' => 4
        ]);

        if ($relacion->save()) {
            if (Yii::$app->request->isAjax) {
                return Json::encode('Has comenzado a seguir al crítico');
            }
            Yii::$app->session->setFlash('success', 'Has comenzado a seguir al crítico');
            return $this->redirect(['index-filtrado', 'texto' => false, 'tipoLista' => 'criticos']);
        }

        if (Yii::$app->request->isAjax) {
            return Json::encode('Ha ocurrido un error al intentar seguir al crítico');
        }
        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al intentar seguir al crítico');
        return $this->redirect(['index-filtrado', 'texto' => false, 'tipoLista' => 'criticos']);
    }

    /**
     * Permite prescindir de la relación de seguimiento del usuario
     * crítico de juegos.
     *
     * @param integer $uId
     * @throws ForbiddenHttpException si no supera las reglas de acceso
     */
    public function actionAbandonarCritico($uId)
    {
        $relacion = Relaciones::find()
        ->where([
            'usuario1_id' => Yii::$app->user->id,
            'usuario2_id' => $uId,
            'estado' => 4
        ])->one();

        if ($relacion->delete()) {
            if (Yii::$app->request->isAjax) {
                return Json::encode('Has dejado de seguir al crítico');
            }

            Yii::$app->session->setFlash('success', 'Has dejado de seguir al crítico');
            return $this->redirect(['index-filtrado', 'texto' => false, 'tipoLista' => 'criticos']);
        }

        if (Yii::$app->request->isAjax) {
            return Json::encode('Ha ocurrido un error al intentar abandonar al crítico');
        }

        Yii::$app->session->setFlash('success', 'Ha ocurrido un error al intentar abandonar al crítico');
        return $this->redirect(['index-filtrado', 'texto' => false, 'tipoLista' => 'criticos']);
    }

    /**
     * Renderiza una vista con la que el usuario puede seleccionar entre
     * varias imagenes de usuario, para asignarse como imagen de usuario.
     *
     * @param integer $id id de usuario
     * @return boolean|string
     */
    public function actionCambioImagen($id)
    {
        $model = $this->findModel($id);
        
        if (!empty(Yii::$app->request->post())) {
            $model->img_key = Yii::$app->request->post()['img_key'];
            $model->scenario = Usuarios::SCENARIO_UPDATE;
            $model->password = '';
            Yii::debug($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Se ha guardado la imagen correctamente');
                return false;
            }
            Yii::$app->session->setFlash('error', 'Algo ha fallado al guardar la imagen');
        }

        return $this->render('cambio-imagen', [
            'model' => $model,
            's3' => $this->clienteS3(),
        ]);
    }

    /**
     * Permite al usuario seleccionar un fondo de perfil, renderiza una
     * vista con las opciones de imagenes por defecto.
     *
     * @param integer $id id del usuario
     * @return boolean|string
     */
    public function actionCambioFondo($id)
    {
        $model = $this->findModel($id);
        
        if (!empty(Yii::$app->request->post())) {
            $model->fondo_key = Yii::$app->request->post()['fondo_key'];
            $model->scenario = Usuarios::SCENARIO_UPDATE;
            $model->password = '';
            Yii::debug($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Se ha guardado la imagen correctamente');
                return false;
            }
            Yii::$app->session->setFlash('error', 'Algo ha fallado al guardar la imagen');
        }

        return $this->render('cambio-fondo', [
            'model' => $model,
            's3' => $this->clienteS3(),
        ]);
    }

    /**
     * Permite al usuario seleccionar sus generos de preferencia
     * genera una vista con las opciones de géneros.
     *
     * @return Response
     */
    public function actionAnadirPreferencias()
    {
        $model = $this->findModel(Yii::$app->user->id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $usuario = Yii::$app->request->post('Usuarios');
            if ($usuario['etiquetas']) {
                foreach ($model->etiquetas as $etiqueta) {
                    $model->unlink('etiquetas', $etiqueta, true);
                }
                foreach ($usuario['etiquetas'] as $idEtiqueta) {
                    if (in_array($idEtiqueta, $model->generosPreferencia(true))) {
                        continue;
                    }
                    $model->link('etiquetas', Etiquetas::findOne($idEtiqueta));
                }
                Yii::$app->session->setFlash('success', 'Los generos de preferencia se han guardado correctamente');
                return $this->redirect(['view', 'id' => Yii::$app->user->id]);
            }
        }

        foreach (Etiquetas::find()->all() as $etiqueta) {
            $generosArray[$etiqueta->id] = $etiqueta->nombre;
        }

        return $this->render('generos-preferencia', [
            'model' => $model,
            'generosArray' => $generosArray,
        ]);
    }

    /**
     * Encuentra el modelo Usuarios basado en la clave primaria.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     * @param int $id
     * @return Usuarios el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Usuarios::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }

    /**
     * Envia el correo de verificación que es producto de la accion
     * de solicitud de verificación de usuario.
     *
     * @param integer $usuarioId el id del usuario al que se le envía el correo
     * @return void
     */
    public function enviaCorreoConfirmacion($usuarioId)
    {
        Yii::$app->mailer->compose()
        ->setFrom('gamesandfriends2@gmail.com')
        ->setTo($this->findModel($usuarioId)->email)
        ->setSubject('Confirmacion de registro')
        ->setHtmlBody(
            'Confirma tu correo electronico con el siguiente enlace: '
            . Html::a(
                'Confirmar',
                Url::to(
                    [
                        'usuarios/verificar',
                        'token' => $this->findModel($usuarioId)->token,
                    ],
                    true
                )
            )
        )->send();

        Yii::$app->session->setFlash('success', 'Se ha enviado el correo de confirmacion');
    }

    /**
     * Envia un correo de bienvenida al usuario recien registrado.
     * Funciona tambien como un correo de verificación.
     *
     * @param integer $usuarioId el usuario al que envia el correo
     * @return void
     */
    public function enviaCorreoBienvenida($usuarioId)
    {
        Yii::$app->mailer->compose(
            'bienvenida',
            [
                'nombre' => $this->findModel($usuarioId)->nombre,
                'token' => $this->findModel($usuarioId)->token,
            ]
        )->setFrom('gamesandfriends2@gmail.com')
        ->setTo($this->findModel($usuarioId)->email)
        ->setSubject('Bienvenid@ a GamesandFriends')
        ->send();

        Yii::$app->session->setFlash('success', 'Se ha enviado el correo de confirmacion');
    }

    /**
     * Envia un correo con la petición de amistad al usuario objetivo
     * que podrá aceptar.
     *
     * @param integer $amigoId el id del usuario objetivo
     * @return boolean si se envía correctamente el email
     */
    private function enviaPeticionAmistad($amigoId)
    {
        $correo = Yii::$app->mailer->compose()
        ->setFrom('gamesandfriends2@gmail.com')
        ->setTo($this->findModel($amigoId)->email)
        ->setSubject('Peticion de amistad de ' . $this->findModel(Yii::$app->user->id)->nombre)
        ->setHtmlBody('Para aceptar la peticion, pulsa '
        . Html::a('aqui', Url::to(['usuarios/anadir-amigo', 'usuarioId' => Yii::$app->user->id, 'amigoId' => $amigoId], true)) . '.');

        if ($correo->send()) {
            Yii::$app->session->setFlash('info', 'Se ha mandado la peticion de amistad');
            return true;
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al mandar la peticion de amistad');
        return false;
    }

    /**
     * Función que actualiza el orden de la lista de juegos deseados
     * que se llama cuando un juego es retirado de la lista de deseos.
     *
     * @param integer $uId el id del usuario cuya lista se va a ordenar
     * @return boolean|Response si se actualiza correctamente
     */
    private function actualizarOrdenDeseados($uId)
    {
        $deseados = Deseados::find()
        ->where(['usuario_id' => $uId])
        ->orderBy('orden')
        ->all();

        if (!$deseados) {
            return false;
        }

        for ($i = 0; $i < count($deseados); $i++) {
            $deseo = $deseados[$i];
            $deseo->orden = $i+1;
            if (!$deseo->save()) {
                Yii::$app->session->setFlash('error', 'Ha ocurrido un error actualizando el orden de la lista de deseos');
                return $this->redirect(['ver-lista-deseos', 'uId' => $uId]);
            }
        }

        return true;
    }

    /**
     * Función que devuelve el cliente de Amazon S3 que sirve las imagenes de usuario/fondo de usuario
     *
     * @return S3Client
     */
    private function clienteS3()
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-3',
            'credentials' => [
                'key' => getenv('KEY'),
                'secret' => getenv('SECRET'),
                'token' => null,
                'expires' => null,
            ],
        ]);

        return $s3;
    }
}
