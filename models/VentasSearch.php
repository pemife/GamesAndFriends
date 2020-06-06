<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * VentasSearch representa el modelo tras el formulario de busqueda de `app\models\Ventas`.
 */
class VentasSearch extends Ventas
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'vendedor_id', 'comprador_id', 'producto_id', 'copia_id'], 'integer'],
            [['created_at', 'finished_at'], 'safe'],
            [['precio'], 'number'],
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
     * Creates data provider instance with search query applied.
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Ventas::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['titulo'] = [
            'asc' => ['titulo' => SORT_ASC],
            'desc' => ['titulo' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'finished_at' => $this->finished_at,
            'vendedor_id' => $this->vendedor_id,
            'comprador_id' => $this->comprador_id,
            'producto_id' => $this->producto_id,
            'copia_id' => $this->copia_id,
            'precio' => $this->precio,
        ]);

        return $dataProvider;
    }
}
