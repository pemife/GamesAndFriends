<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Posts;

/**
 * PostsSearch representa el modelo tras el formulario de busqueda de `app\models\Posts`.
 */
class PostsSearch extends Posts
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'juego_id', 'usuario_id'], 'integer'],
            [['titulo', 'created_at', 'media', 'desarrollo'], 'safe'],
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
        $query = Posts::find();

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
            'juego_id' => $this->juego_id,
            'usuario_id' => $this->usuario_id,
        ]);

        $query->andFilterWhere(['ilike', 'titulo', $this->titulo])
            ->andFilterWhere(['ilike', 'media', $this->media])
            ->andFilterWhere(['ilike', 'desarrollo', $this->desarrollo]);

        return $dataProvider;
    }
}
