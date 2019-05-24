<?php
/**
 * Created by PhpStorm.
 * User: singletonn
 * Date: 5/7/18
 * Time: 2:26 PM
 */

namespace pantera\yii2\pay\sberbank\components;

use pantera\yii2\pay\sberbank\models\Invoice;
use pantera\yii2\pay\sberbank\Module;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\helpers\Url;

class Sberbank extends Component
{
    /* @var Module */
    private $module;
    /**
     * @var string Логин в сбербанке
     */
    public $login;
    /**
     * @var string Пароль в сбербанке
     */
    public $password;
    /**
     * @var string Адрес платежного шлюза
     */
    public $url = 'https://securepayments.sberbank.ru/payment/rest/';
    /**
     * @var string Тестовый адрес платежного шлюза
     */
    public $urlTest = 'https://3dsec.sberbank.ru/payment/rest/';
    /**
     * @var bool Если true будет использован тестовый сервер
     */
    public $testServer = false;
    /**
     * @var string Акшион сбербанка для регистрации оплаты
     */
    public $actionRegister = 'register.do';
    /**
     * @var string Ашион сбербанка для получения статуса оплаты
     */
    public $actionStatus = 'getOrderStatus.do';
    /* @var int Время жизни заказа в секундах */
    public $sessionTimeoutSecs = 1200;
    /**
     * @var string Url адрес страницы для возврата с платежного шлюза
     * необходимо указывать без host
     */
    public $returnUrl = '/sberbank/default/complete';

    public function init()
    {
        parent::init();
        $this->module = Module::getInstance();
        if (empty($this->login)
            || empty($this->password)
            || empty($this->url)
            || empty($this->actionRegister)
            || empty($this->actionStatus)
            || empty($this->returnUrl)) {
            throw new InvalidConfigException('Модуль настроен не правильно пожалуйсто прочтите документацию');
        }
        if ($this->testServer && empty($this->urlTest)) {
            throw new InvalidConfigException('Включен тестовый режим но тестовый адрес сбербанка пустой');
        }
    }

    public function create(Invoice $model)
    {
        $post = [];
        $post['orderNumber'] = $model->data['uniqid'];
        $post['amount'] = $model->sum * 100;
        $post['returnUrl'] = Url::to($this->returnUrl, true);
        $post['sessionTimeoutSecs'] = $this->sessionTimeoutSecs;
        if (array_key_exists('comment', $model->data)) {
            $post['description'] = $model->data['comment'];
        }
        $result = $this->send($this->actionRegister, $post);
        if (array_key_exists('formUrl', $result)) {
            $model->url = $result['formUrl'];
            $model->save();
        }
        return $result;
    }

    public function complete($orderId)
    {
        $post = [];
        $post['orderId'] = $orderId;
        return $this->send($this->actionStatus, $post);
    }

    /**
     * Откправка запроса в api сбербанка
     * @param $action string Акшион на который отпровляем запрос
     * @param $data array Параметры которые передаём в запрос
     * @return mixed Ответ сбербанка
     */
    private function send($action, $data)
    {
        $data['userName'] = $this->login;
        $data['password'] = $this->password;
        $url = ($this->testServer ? $this->urlTest : $this->url) . $action;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $out = curl_exec($curl);
        curl_close($curl);
        return Json::decode($out);
    }
}
