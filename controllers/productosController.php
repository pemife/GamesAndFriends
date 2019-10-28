<?php

namespace app\controllers;

class productosController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
