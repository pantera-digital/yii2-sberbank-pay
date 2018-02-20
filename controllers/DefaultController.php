<?php

namespace pantera\yii2\pay\sberbank\controllers;

use pantera\yii2\pay\sberbank\models\Invoice;
use pantera\yii2\pay\sberbank\Module;
use Yii;
use yii\base\ErrorException;
use yii\db\Expression;
use yii\helpers\Json;
use yii\helpers\Url;
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
        if(is_null(Yii::$app->request->get('orderId'))){
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
        $post = [];
        $post['orderId'] = Yii::$app->request->get('orderId');
        $result = $this->sendApi($this->module->actionStatus, $post);
        //Проверяем статус оплаты если всё хорошо обновим инвойс и редерекним
        if (isset($result['OrderStatus']) && ($result['OrderStatus'] == 2)) {
            $model->status = "S";
            $model->pay_time = new Expression("NOW()");
            $model->update();
            if ($this->module->successCallback) {
                $callback = $this->module->successCallback;
                $callback($model);
            }
            $this->redirect($this->module->successUrl);
        } else {
            $this->redirect($this->module->failUrl);
        }
    }


    /**
     * Создание оплаты редеректим в шлюз сберабнка
     * @param $id
     * @return \yii\web\Response
     * @throws ErrorException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreate($id)
    {
        $model = Invoice::findOne($id);
        $result = $this->createOrder($model);
        if (array_key_exists('errorCode', $result)) {
            throw new ErrorException($result['errorMessage']);
        }
        $orderId = $result['orderId'];
        $formUrl = $result['formUrl'];
        $model->orderId = $orderId;
        $model->update();
        return $this->redirect($formUrl);
    }

    /**
     * Создание массива с данными для создания оплаты
     * @param $model
     * @return mixed
     */
    private function createOrder($model)
    {
        $post = [];
        $post['orderNumber'] = $model->order_id;
        $post['amount'] = $model->sum * 100;
        $post['returnUrl'] = Url::to($this->module->returnUrl, true);
        return $this->sendApi($this->module->actionRegister, $post);
    }

    /**
     * Откправка запроса в api сбербанка
     * @param $action Акшион на который отпровляем запрос
     * @param $data Параметры которые передаём в запрос
     * @return mixed Ответ сбербанка
     */
    private function sendApi($action, $data)
    {
        $data['userName'] = $this->module->login;
        $data['password'] = $this->module->password;
        $url = $this->module->url . $action;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $out = curl_exec($curl);
        curl_close($curl);;
        return Json::decode($out);
    }
}