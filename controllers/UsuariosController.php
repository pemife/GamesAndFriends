<?php

namespace app\controllers;

use app\models\Copias;
use app\models\LoginForm;
use app\models\Productos;
use app\models\Relaciones;
use app\models\Usuarios;
use app\models\UsuariosSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
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
                'only' => ['create', 'update', 'delete',  'login', 'logout', 'mandar-peticion', 'bloquear-usuario', 'anadir-amigo', 'desbloquear-usuario'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'create'],
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
                            $model = $this->findModel(Yii::$app->request->queryParams['id']);
                            if (Yii::$app->user->id === 1) {
                                return true;
                            }

                            if ($model->id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', '¡No puedes modificar el perfil de otra persona!');
                                return false;
                            }

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡No puedes modificar perfiles sin iniciar sesión!');
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
                        'actions' => ['anadir-amigo'],
                        'matchCallback' => function ($rule, $action) {
                            $usuarioId = Yii::$app->request->queryParams['usuarioId'];
                            $amigoId = Yii::$app->request->queryParams['amigoId'];

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

                            if ($usuario->relacionCon(Yii::$app->user->id)->usuario1_id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes desbloquear a este usuario');
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
     * Updates an existing Usuarios model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
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
     * Deletes an existing Usuarios model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $model->delete();
            
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

    public function actionAnadirInventario()
    {
        return $this->render('anadirInventario');
    }

    public function actionSolicitarVerificacion()
    {
        if (!Yii::$app->user->isGuest) {
            $usuario = $this->findModel(Yii::$app->user->id);
            $usuario->requested_at = date('Y-m-d H:i:s');

            $usuario->scenario = Usuarios::SCENARIO_VERIFICACION;
            if ($usuario->save()) {
                $this->enviaCorreoConfirmacion(Yii::$app->user->id);
            } else {
                Yii::$app->session->setFlash('error', 'Error al solicitar la verificacion');
                Yii::debug($usuario);
            }

            return $this->actionView(Yii::$app->user->id);
        }

        Yii::$app->session->setFlash('error', 'Debes iniciar sesion para solicitar la verificacion de tu cuenta');
        return $this->redirect(['site/login']);
    }

    public function actionVerificar($token)
    {
        if (!Yii::$app->user->isGuest) {
            $usuario = $this->findModel(Yii::$app->user->id);

            $aTiempo = ((new \DateTime())->getTimestamp() - strtotime($usuario->requested_at)) < 3600;

            if ($usuario->token === $token && $aTiempo) {
                $usuario->token = null;

                $usuario->scenario = Usuarios::SCENARIO_VERIFICACION;
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

    public function actionListaAmigos($usuarioId)
    {
        $usuario = $this->findModel($usuarioId);

        return $this->renderAjax('vistaAmigos', [
          'listaAmigos' => $usuario->arrayRelacionados(1),
        ]);
    }

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
                return $this->redirect(['view', 'id' => $amigoId]);
            }
        }
        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al guardar la petición de amistad');
        return $this->redirect(['view', 'id' => $amigoId]);
    }

    public function actionBorrarAmigo($amigoId)
    {
        $usuario = $this->findModel(Yii::$app->user->id);

        if (!$usuario->esAmigo($amigoId)) {
            Yii::$app->session->setFlash('error', 'No sois amigos!');
            return $this->redirect(['view', 'id' => $amigoId]);
        }
        
        $relacion = Relaciones::find()
        ->where(['estado' => 1, 'usuario1_id' => $usuario->id, 'usuario2_id' => $amigoId])
        ->orWhere(['usuario1_id' => $amigoId, 'usuario2_id' => $usuario->id])
        ->one();

        $relacion->delete();

        if ($usuario->esAmigo($amigoId)) {
            Yii::$app->session->setFlash('error', 'Ha ocurrido un error al borrarse como amigo');
            return $this->redirect('view', ['id' => $amigoId]);
        }

        Yii::$app->session->setFlash('success', 'Te has borrado satisfactoriamente como amigo');
        return $this->redirect(['view', 'id' => $amigoId]);
    }

    public function actionBloquearUsuario($usuarioId)
    {
        $usuario = $this->findModel($usuarioId);

        $relacion = $usuario->relacionCon(Yii::$app->user->id);

        if (empty($relacion)) {
            $relacion = new Relaciones([
                'usuario1_id' => Yii::$app->user->id,
                'usuario2_id' => $usuario->id,
                'estado' => 3,
            ]);
        } else {
            if ($relacion->usuario1_id != Yii::$app->user->id) {
                $relacion = new Relaciones([
                    'usuario1_id' => Yii::$app->user->id,
                    'usuario2_id' => $usuario->id,
                    'estado' => 3,
                ]);
            } else {
                $relacion->estado = $relacion->old_estado;
                $relacion->old_estado = 3;
            }
        }

        if ($relacion->save()) {
            Yii::$app->session->setFlash('success', 'Has bloqueado satisfactoriamente a ' . $usuario->nombre);
            return $this->redirect(['view', 'id' => $usuario->id]);
        }

        Yii::$app->session->setFlash('error', 'Ha ocurrido un error al bloquear al usuario ' . $usuario->nombre);
        return $this->redirect(['view', 'id' => $usuario->id]);
    }

    public function actionDesbloquearUsuario($usuarioId)
    {
        $usuario = $this->findModel($usuarioId);

        $relacion = $usuario->relacionCon(Yii::$app->user->id);

        $relacion->delete();
        Yii::$app->session->setFlash('success', 'Has desbloqueado satisfactoriamente a ' . $usuario->nombre);
        return $this->redirect(['view', 'id' => $usuario->id]);
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

    public function enviaCorreoConfirmacion($usuarioId)
    {
        Yii::$app->mailer->compose()
        ->setFrom('gamesandfriends2@gmail.com')
        ->setTo($this->findModel($usuarioId)->email)
        ->setSubject('Confirmacion de registro')
        ->setHtmlBody('Confirma tu correo electronico con el siguiente enlace: '
        . Html::a(
            'Confirmar',
            Url::to(
                [
                    'usuarios/verificar',
                    'token' => $this->findModel($usuarioId)->token,
                ],
                true
            )
        ))->send();

        Yii::$app->session->setFlash('success', 'Se ha enviado el correo de confirmacion');
    }

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
}
