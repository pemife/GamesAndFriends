<?php

namespace app\models;

use Yii;
use yii\db\pgsql\QueryBuilder;
use yii\db\Query;

/**
 * This is the model class for table "posts".
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
     * @return \yii\db\ActiveQuery
     */
    public function getComentarios()
    {
        return $this->hasMany(Comentarios::className(), ['post_id' => 'id'])->inverseOf('post');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJuego()
    {
        return $this->hasOne(Juegos::className(), ['id' => 'juego_id'])->inverseOf('posts');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('posts');
    }

    public function getVotos()
    {
        return VotosPosts::find()->where(['post_id' => $this->id])->count();
    }

    public function usuarioVotado($uId)
    {
        return VotosPosts::find()->where(['post_id' => $this->id, 'usuario_id' => $uId])->exists();
    }
}
