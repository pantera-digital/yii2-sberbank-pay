<?php

namespace pantera\yii2\pay\sberbank\controllers;

use pantera\yii2\pay\sberbank\models\Invoice;
use pantera\yii2\pay\sberbank\Module;
use Yii;
use yii\base\ErrorException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    /* @var Module */
    public $module;

    /**
     * Сюда будет перенаправлен результат оплаты
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionComplete()
    {
        /* @var $model Invoice */
        if (is_null(Yii::$app->request->get('orderId'))) {
            throw new NotFoundHttpException();
        }
        $model = Invoice::find()
            ->where([
                'AND',
                ['=', 'status', 'I'],
                ['=', 'orderId', Yii::$app->request->get('orderId')],
            ])
            ->one();
        if (is_null($model)) {
            throw new NotFoundHttpException();
        }
        $result = $this->module->sberbank->complete(Yii::$app->request->get('orderId'));
        //Проверяем статус оплаты если всё хорошо обновим инвойс и редерекним
        if (isset($result['OrderStatus']) && ($result['OrderStatus'] == $this->module->sberbank->classRegister->successStatus())) {
            $model->attributes = $this->module->sberbank->classRegister->getDataForUpdate();
            $model->update();
            if ($this->module->successCallback) {
                $callback = $this->module->successCallback;
                $callback($model);
            }
            $this->redirect($this->module->successUrl);
        } else {
            if ($this->module->failCallback) {
                $callback = $this->module->failCallback;
                $callback($model);
            }
            $this->redirect($this->module->failUrl);
        }
    }


    /**
     * Создание оплаты редеректим в шлюз сберабнка
     * @param $id
     * @return \yii\web\Response
     * @throws ErrorException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     * @throws \Throwable
     */
    public function actionCreate($id)
    {
        $model = Invoice::findOne($id);
        $result = $this->module->sberbank->create($model);
        if (array_key_exists('errorCode', $result)) {
            throw new ErrorException($result['errorMessage']);
        }
        $orderId = $result['orderId'];
        $formUrl = $result['formUrl'];
        $model->orderId = $orderId;
        $model->update();
        return $this->redirect($formUrl);
    }
}