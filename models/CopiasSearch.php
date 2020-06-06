<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Copias;

/**
 * CopiasSearch representa el modelo tras el formulario de busqueda de `app\models\Copias`.
 */
class CopiasSearch extends Copias
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'juego_id', 'propietario_id', 'plataforma_id'], 'integer'],
            [['clave'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Crea una instancia de proveedor de datos con la query de busqueda aplicada
     *
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Copias::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'juego_id' => $this->juego_id,
            'propietario_id' => $this->propietario_id,
            'plataforma_id' => $this->plataforma_id,
        ]);

        $query->andFilterWhere(['ilike', 'clave', $this->clave]);

        return $dataProvider;
    }
}
