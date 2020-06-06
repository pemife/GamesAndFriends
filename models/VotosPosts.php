<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "votos_posts".
 *
 * @property int $usuario_id
 * @property int $post_id
 *
 * @property Posts $post
 * @property Usuarios $usuario
 */
class VotosPosts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'votos_posts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usuario_id', 'post_id'], 'required'],
            [['usuario_id', 'post_id'], 'default', 'value' => null],
            [['usuario_id', 'post_id'], 'integer'],
            [['usuario_id', 'post_id'], 'unique', 'targetAttribute' => ['usuario_id', 'post_id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Posts::className(), 'targetAttribute' => ['post_id' => 'id']],
            [['usuario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Usuarios::className(), 'targetAttribute' => ['usuario_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'usuario_id' => 'Usuario ID',
            'post_id' => 'Post ID',
        ];
    }

    /**
     * Devuelve query para [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Posts::className(), ['id' => 'post_id'])->inverseOf('votosPosts');
    }

    /**
     * Devuelve query para [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('votosPosts');
    }
}
