<?php

namespace app\models;

use Yii;

/**
 * Esta es la clase modelo para la tabla "reportes_comentarios".
 *
 * @property int $usuario_id
 * @property int $comentario_id
 * @property string|null $razon
 *
 * @property Comentarios $comentario
 * @property Usuarios $usuario
 */
class ReportesComentarios extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reportes_comentarios';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['usuario_id', 'comentario_id'], 'required'],
            [['usuario_id', 'comentario_id'], 'default', 'value' => null],
            [['usuario_id', 'comentario_id'], 'integer'],
            [['razon'], 'string'],
            [['usuario_id', 'comentario_id'], 'unique', 'targetAttribute' => ['usuario_id', 'comentario_id']],
            [['comentario_id'], 'exist', 'skipOnError' => true, 'targetClass' => Comentarios::className(), 'targetAttribute' => ['comentario_id' => 'id']],
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
            'comentario_id' => 'Comentario ID',
            'razon' => 'Razon',
        ];
    }

    /**
     * Devuelve el comentario del reporte.
     *
     * @return Comentarios
     */
    public function getComentario()
    {
        return $this->hasOne(Comentarios::className(), ['id' => 'comentario_id'])->inverseOf('reportesComentarios');
    }

    /**
     * Devuelve el usuario del reporte.
     *
     * @return Usuarios
     */
    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['id' => 'usuario_id'])->inverseOf('reportesComentarios');
    }
}
