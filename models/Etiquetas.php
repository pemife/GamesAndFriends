<?php

namespace app\models;

/**
 * This is the model class for table "etiquetas".
 *
 * @property int $id
 * @property string $nombre
 *
 * @property JuegosEtiquetas[] $juegosEtiquetas
 * @property UsuariosEtiquetas[] $usuariosEtiquetas
 */
class Etiquetas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'etiquetas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 20],
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
        ];
    }

    /**
     * Gets query for [[Juegos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getJuegos()
    {
        return $this->hasMany(Juegos::className(), ['id' => 'juego_id'])->viaTable('juegos_etiquetas', ['etiqueta_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsuariosEtiquetas()
    {
        return $this->hasMany(UsuariosEtiquetas::className(), ['etiqueta_id' => 'id'])->inverseOf('etiqueta');
    }
}
