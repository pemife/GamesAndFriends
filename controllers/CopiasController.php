<?php

namespace app\controllers;

use app\models\Copias;
use app\models\CopiasSearch;
use app\models\Juegos;
use app\models\Plataformas;
use app\models\Usuarios;
use app\models\Ventas;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CopiasController implements the CRUD actions for Copias model.
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
                'only' => ['create', 'update', 'delete', 'mis-copias', 'view', 'finalizar-regalo', 'regalar-copia', 'crear-regalo'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'mis-copias'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'matchCallback' => function ($rule, $action) {

                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡Debes iniciar sesión para ver este contenido!');
                                return false;
                            }

                            $copia = $this->findModel(Yii::$app->request->queryParams['id']);
                            if (Yii::$app->user->id != $copia->propietario_id) {
                                Yii::$app->session->setFlash('error', '¡No puedes ver esa copia!');
                                return false;
                            }

                            return true;
                        },
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

                            if (Ventas::find()->where(['copia_id' => $model->id])) {
                                Yii::$app->session->setFlash('error', 'No puedes modificar/borrar una copia que esta en venta');
                                return false;
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['regalar-copia'],
                        'matchCallback' => function ($rule, $action) {
                            
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', 'No puedes regalar nada sin iniciar sesion');
                                return false;
                            }
                            
                            if (Yii::$app->request->post()['uId'] == Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes regalarte nada a ti mismo');
                                return false;
                            }
                            
                            $copia = Copias::findOne(Yii::$app->request->post()['cId']);
                            if (!$copia) {
                                Yii::$app->session->setFlash('error', 'No puedes regalar una copia que no existe!');
                                return false;
                            }

                            if ($copia->propietario_id != Yii::$app->user->id) {
                                Yii::$app->session->setFlash('error', 'No puedes regalar una copia que no te pertenece!');
                                return false;
                            }

                            $usuario = Usuarios::findOne(Yii::$app->request->post()['uId']);
                            if (!$usuario) {
                                Yii::$app->session->setFlash('error', 'No puedes regalarle nada a un usuario que no existe!');
                                return false;
                            }
                            
                            if (!$usuario->esVerificado()) {
                                Yii::$app->session->setFlash('error', 'No puedes regalar nada a un usuario que no esta verificado!');
                                return false;
                            }
                            
                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['finalizar-regalo'],
                        'matchCallback' => function ($rule, $action) {
                            return true;
                        }
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all Copias models.
     * @return mixed
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
     * Displays a single Copias model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'amigos' => Usuarios::findOne(Yii::$app->user->id)->arrayRelacionados(1),
        ]);
    }

    /**
     * Creates a new Copias model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Copias();

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
     * Updates an existing Copias model.
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
     * Deletes an existing Copias model.
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

    // Esta accion dejará la copia sin dueño temporalmente y
    // mandará un email al usuario receptor para que acepte/rechace el regalo
    public function actionRegalarCopia()
    {
        $post = Yii::$app->request->post();
        $cId = $post['cId'];
        $uId = $post['uId'];
        $copia = $this->findModel($cId);
        $this->correoRegalo($copia, $uId);
        return true;
    }

    // $cId, $uId, $acepta (post)
    public function actionFinalizarRegalo()
    {
        var_dump(Yii::$app->request->post());
        exit;
    }

    /**
     * Finds the Copias model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Copias the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Copias::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function correoRegalo($copia, $uId)
    {
        $regalo = [
            'regaladorId' => Yii::$app->user->id,
            'receptorId' => $uId,
            'cId' => $copia->id
        ];

        $enlaceAcepta = Html::a(
            'este',
            Url::to(
                [
                    'copias/finalizar-regalo',
                ],
                true
            ),
            [
                'data' => [
                    'method' => 'POST',
                    'params' => [
                        'regalo' => $regalo,
                        'acepta' => true
                    ]
                ]
            ]
        );
        $enlaceRechaza =  Html::a(
            'este',
            Url::to(
                [
                    'copias/finalizar-regalo',
                ],
                true
            ),
            [
                'class' => 'btn btn-primary',
                'data' => [
                    'method' => 'POST',
                    'params' => [
                        'regalo' => $regalo,
                        'acepta' => false
                    ]
                ]
            ]
        );

        Yii::$app->mailer->compose()
        ->setFrom('gamesandfriends2@gmail.com')
        ->setTo(Usuarios::findOne($uId)->email)
        ->setSubject('¡Has recibido un regalo!')
        ->setHtmlBody(
            'El usuario ' . Html::encode(Usuarios::findOne(Yii::$app->user->id)->nombre)
            . ' te ha regalado una copia de '
            . $copia->juego->titulo
            . '<br><br>Para <b>aceptar</b> el regalo, pulsa ' . $enlaceAcepta . ' enlace.'
            . '<br><br>Paca <b>rechazar</b> el regalo, pulsa ' . $enlaceRechaza . ' enlace.'
        )->send();

        Yii::$app->session->setFlash('success', 'Se ha enviado el correo del regalo');
    }
}
