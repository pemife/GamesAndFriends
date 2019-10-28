<?php

namespace app\models;

/**
 * This is the model class for table "juegos".
 *
 * @property int $id
 * @property string $titulo
 * @property string $descripcion
 * @property string $fechalan
 * @property string $dev
 *
 * @property JuegosEtiquetas[] $juegosEtiquetas
 * @property Posts[] $posts
 * @property Productos[] $productos
 */
class Juegos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'juegos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'dev'], 'required'],
            [['descripcion'], 'string'],
            [['fechalan'], 'safe'],
            [['titulo', 'dev'], 'string', 'max' => 255],
            [['dev'], 'unique'],
            [['titulo'], 'unique'],
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
            'descripcion' => 'Descripcion',
            'fechalan' => 'Fechalan',
            'dev' => 'Dev',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEtiquetas()
    {
        return $this->hasMany(Etiquetas::className(), ['id' => 'etiqueta_id'])->viaTable('juegos_etiquetas', ['juego_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductos()
    {
        return $this->hasMany(Productos::className(), ['juego_id' => 'id'])->inverseOf('juego');
    }
}
