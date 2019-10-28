<?php

namespace app\models;

/**
 * This is the model class for table "usuarios".
 *
 * @property int $id
 * @property string $nombre
 * @property string $password
 * @property string $created_at
 * @property string $token
 * @property string $email
 * @property string $biografia
 * @property string $fechanac
 *
 * @property Comentarios[] $comentarios
 * @property Criticas[] $criticas
 * @property Posts[] $posts
 * @property UsuariosEtiquetas[] $usuariosEtiquetas
 */
class Usuarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'password', 'email'], 'required'],
            [['created_at', 'fechanac'], 'safe'],
            [['biografia'], 'string'],
            [['nombre', 'token'], 'string', 'max' => 32],
            [['password'], 'string', 'max' => 60],
            [['email'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['nombre'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'password' => 'Password',
            'created_at' => 'Created At',
            'token' => 'Token',
            'email' => 'Email',
            'biografia' => 'Biografia',
            'fechanac' => 'Fechanac',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComentarios()
    {
        return $this->hasMany(Comentarios::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCriticas()
    {
        return $this->hasMany(Criticas::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['usuario_id' => 'id'])->inverseOf('usuario');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('usuarios_etiquetas', ['usuario_id' => 'id']);
    }
}
