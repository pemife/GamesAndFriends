<?php

namespace app\models;

use Yii;
use yii\db\pgsql\QueryBuilder;
use yii\db\Query;

/**
 * Esta es una clase modelo para la tabla "posts".
 *
 * @property int $id
 * @property string $titulo
 * @property string $created_at
 * @property string $media
 * @property string $desarrollo
 * @property int $juego_id
 * @property int $usuario_id
 *
 * @property Comentarios[] $comentarios
 * @property Juegos $juego
 * @property Usuarios $usuario
 */
class Posts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'juego_id', 'usuario_id'], 'required'],
            [['created_at'], 'safe'],
            [['desarrollo'], 'string'],
            [['juego_id', 'usuario_id'], 'default', 'value' => null],
            [['juego_id', 'usuario_id'], 'integer'],
            [['titulo', 'media'], 'string', 'max' => 255],
            [['juego_id'], 'exist', 'skipOnError' => true, 'targetClass' => Juegos::className(), 'targetAttribute' => ['juego_id' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Titulo',
            'created_at' => 'Created At',
            'media' => 'Media',
            'desarrollo' => 'Desarrollo',
            'juego_id' => 'Juego ID',
            'usuario_id' => 'Usuario ID',
        ];
    }

    /**
     * Devuelve query para [[Comentarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComentarios()
    {
        return $this->hasMany(Comentarios::className(), ['post_id' => 'id'])->inverseOf('post');
    }

    /**
     * Devuelve el juego asociado al post
     *
     * @return \yii\db\ActiveRecord
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('posts');
    }

    /**
     * Devuelve el usuario asociado al post
     *
     * @return \yii\db\ActiveRecord
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('posts');
    }

    /**
     * Devuelve el numero del total de votos del post
     *
     * @return integer
     */
    public function getVotos()
    {
        return VotosPosts::find()->where(['post_id' => $this->id])->count();
    }

    /**
     * Devuelve si el usuario ha votado el post o no
     *
     * @param [integer] $uId el id del usuario que estamos comprobando
     * @return boolean si ha votado el post o no
     */
    public function usuarioVotado($uId)
    {
        return VotosPosts::find()->where(['post_id' => $this->id, 'usuario_id' => $uId])->exists();
    }
}
