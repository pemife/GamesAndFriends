<?php

namespace app\controllers;

use app\models\Comentarios;
use app\models\Juegos;
use app\models\Posts;
use app\models\PostsSearch;
use app\models\VotosPosts;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * PostsController implementa las acciones CRUD para el modelo Posts.
 */
class PostsController extends Controller
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
                'only' => ['create', 'update', 'delete', 'votar'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'matchCallback' => function ($rule, $action) {
                            $model = $this->findModel(Yii::$app->request->queryParams['id']);
                            if (!Yii::$app->user->isGuest && ($model->usuario->id == Yii::$app->user->id)) {
                                return true;
                            }
                            Yii::$app->session->setFlash('error', '¡No puedes modificar el post de otra persona!');
                            return false;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['votar'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->isGuest) {
                                Yii::$app->session->setFlash('error', '¡No puedes votar sin iniciar sesion!');
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
     * Lista todos los modelos Posts
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Muestra un único modelo Posts.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    public function actionView($id)
    {
        $usuarioVotado = Yii::$app->user->isGuest ? false : $this->findModel($id)->usuarioVotado(Yii::$app->user->id);

        $queryComentarios = Comentarios::find()->where(['post_id' => $id]);

        $comentariosProvider = new ActiveDataProvider([
            'query' => $queryComentarios,
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'usuarioHaVotado' => $usuarioVotado,
            'comentariosProvider' => $comentariosProvider,
        ]);
    }

    /**
     * Crea un nuevo modelo Posts.
     * Si la creacion es exitosa, redirecciona a la pagina de vista.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Posts();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'listaJuegos' => Juegos::listaAsociativa(),
        ]);
    }

    /**
     * Actualiza un modelo Posts.
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

        return $this->render('update', [
            'model' => $model,
            'listaJuegos' => Juegos::listaAsociativa(),
        ]);
    }

    /**
     * Borra un modelo Posts.
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
     * Encuentra el modelo Posts basado en su clave primaria.
     * Si el modelo no se encuentra, una excepcion HTTP 404 se lanzará.
     * @param int $id
     * @return Posts el modelo cargado
     * @throws NotFoundHttpException si el modelo no se encuentra
     */
    protected function findModel($id)
    {
        if (($model = Posts::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La pagina solicitada no existe');
    }
    
    /**
     * Funcion para votar posts
     *
     * @return integer el numero de votos total despues de votar el post
     */
    public function actionVotar()
    {
        $requestPost = Yii::$app->request->post();
        $uId = $requestPost['uId'];
        $pId = $requestPost['pId'];
        

        $voto = VotosPosts::find()->where(['usuario_id' => $uId, 'post_id' => $pId])->one();
        // Si el usuario habia votado, retira el voto
        if ($voto) {
            $voto->delete();

            return $this->findModel($pId)->votos;
        }

        $voto = new VotosPosts([
            'post_id' => $pId,
            'usuario_id' => $uId
        ]);

        if (!$voto->save()) {
            return $this->findModel($pId)->votos;
        }
        return $this->findModel($pId)->votos;
    }
}
