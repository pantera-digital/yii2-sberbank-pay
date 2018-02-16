<?php

namespace pantera\yii2\pay\sberbank\controllers;

use pantera\yii2\pay\sberbank\models\Invoice;
use pantera\yii2\pay\sberbank\Module;
use yii\db\Expression;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    /* @var Module */
    public $module;

    public function actionComplete()
    {
        /* @var $model Invoice */
        $model = Invoice::find()
            ->where([
                'AND',
                ['=', 'status', 'I'],
                ['=', 'orderId', $_GET['orderId']]
            ])
            ->one();
        if (is_null($model)) {
            throw new NotFoundHttpException();
        }
        $post = [];
        $post['orderId'] = $_GET['orderId'];
        $result = $this->sendApi($this->module->actionStatus, $post);
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

    public function actionCreate($id)
    {
        $model = Invoice::findOne($id);
        $result = $this->createOrder($model);
        $orderId = $result['orderId'];
        $formUrl = $result['formUrl'];
        $model->orderId = $orderId;
        $model->update();
        return $this->redirect($formUrl);
    }

    private function createOrder($model)
    {
        $post = [];
        $post['orderNumber'] = $model->order_id;
        $post['amount'] = $model->sum * 100;
        $post['returnUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . '/sbpay/default/complete';
        return $this->sendApi($this->module->actionRegister, $post);
    }

    private function sendApi($action, $data)
    {
        $data['userName'] = $this->module->login;
        $data['password'] = $this->module->password;
        $url = $this->module->url . $action;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $out = curl_exec($curl);
        curl_close($curl);;
        return Json::decode($out);
    }
}