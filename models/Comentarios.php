<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comentarios".
 *
 * @property int $id
 * @property string $created_at
 * @property string $texto
 * @property int $usuario_id
 * @property int $post_id
 *
 * @property Posts $post
 * @property Usuarios $usuario
 * @property ReportesComentarios[] $reportesComentarios
 * @property Usuarios[] $usuarios
 */
class Comentarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comentarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['texto', 'usuario_id', 'post_id'], 'required'],
            [['texto'], 'string'],
            [['usuario_id', 'post_id'], 'default', 'value' => null],
            [['usuario_id', 'post_id'], 'integer'],
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
            'id' => 'ID',
            'created_at' => 'Created At',
            'texto' => 'Texto',
            'usuario_id' => 'Usuario ID',
            'post_id' => 'Post ID',
        ];
    }

    /**
     * Gets query for [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Posts::className(), ['id' => 'post_id'])->inverseOf('comentarios');
    }

    /**
     * Gets query for [[Usuario]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('comentarios');
    }

    /**
     * Gets query for [[ReportesComentarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReportesComentarios()
    {
        return $this->hasMany(ReportesComentarios::className(), ['comentario_id' => 'id'])->inverseOf('comentario');
    }

    /**
     * Gets query for [[Usuarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarios()
    {
        return $this->hasMany(Usuarios::className(), ['id' => 'usuario_id'])->viaTable('reportes_comentarios', ['comentario_id' => 'id']);
    }
}
