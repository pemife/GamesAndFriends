<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Comentarios;

/**
 * ComentariosSearch representa el modelo tras el formulario de busqueda de  `app\models\Comentarios`.
 */
class ComentariosSearch extends Comentarios
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'usuario_id', 'post_id'], 'integer'],
            [['created_at', 'texto'], 'safe'],
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
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Comentarios::find();

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
            'usuario_id' => $this->usuario_id,
            'post_id' => $this->post_id,
        ]);

        $query->andFilterWhere(['ilike', 'texto', $this->texto]);

        return $dataProvider;
    }
}
