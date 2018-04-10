<?php
/**
 * Created by PhpStorm.
 * User: singletonn
 * Date: 3/31/18
 * Time: 4:07 PM
 */

namespace pantera\yii2\pay\sberbank\controllers;


use pantera\yii2\pay\sberbank\models\Invoice;
use pantera\yii2\pay\sberbank\Module;
use yii\helpers\Json;
use yii\helpers\Url;

trait OrderTrait
{
    /* @var Module */
    public $module;

    /**
     * Создание массива с данными для создания оплаты
     * @param $model
     * @return mixed
     */
    private function createOrder(Invoice $model)
    {
        $post = [];
        $post['orderNumber'] = $model->data['uniqid'];
        $post['amount'] = $model->sum * 100;
        $post['returnUrl'] = Url::to($this->module->returnUrl, true);
        $post['sessionsTimeoutSecs'] = $this->module->sessionsTimeoutSecs;
        $result = $this->sendApi($this->module->actionRegister, $post);
        if (array_key_exists('formUrl', $result)) {
            $model->url = $result['formUrl'];
            $model->save();
        }
        return $result;
    }

    /**
     * Откправка запроса в api сбербанка
     * @param $action string Акшион на который отпровляем запрос
     * @param $data array Параметры которые передаём в запрос
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