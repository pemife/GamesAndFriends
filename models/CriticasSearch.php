<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Criticas;

/**
 * CriticasSearch represents the model behind the search form of `app\models\Criticas`.
 */
class CriticasSearch extends Criticas
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'usuario_id', 'producto_id'], 'integer'],
            [['opinion', 'created_at'], 'safe'],
            [['valoracion'], 'number'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Criticas::find();

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
            'created_at' => $this->created_at,
            'valoracion' => $this->valoracion,
            'usuario_id' => $this->usuario_id,
            'producto_id' => $this->producto_id,
        ]);

        $query->andFilterWhere(['ilike', 'opinion', $this->opinion]);

        return $dataProvider;
    }
}
