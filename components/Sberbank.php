<?php

namespace pantera\yii2\pay\sberbank\components;

use pantera\yii2\pay\sberbank\components\registers\RegisterDo;
use pantera\yii2\pay\sberbank\components\registers\RegisterPreAuthDo;
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

    /**
     * @var bool Использовать или нет двухстадийную оплату.
     * По умолчанию - нет.
     */
    public $registerPreAuth = false;

    /**
     * Класс для регистрации оплаты.
     * @var \pantera\yii2\pay\sberbank\components\registers\RegisterInterface
     */
    public $classRegister;

    public function init()
    {
        parent::init();
        $this->module = Module::getInstance();
        if (empty($this->login)
            || empty($this->password)
            || empty($this->url)
            || empty($this->actionStatus)
            || empty($this->returnUrl)) {
            throw new InvalidConfigException('Модуль настроен не правильно пожалуйсто прочтите документацию');
        }
        if ($this->testServer && empty($this->urlTest)) {
            throw new InvalidConfigException('Включен тестовый режим но тестовый адрес сбербанка пустой');
        }
        if ($this->registerPreAuth === false) {
            $this->classRegister = new RegisterDo();
        } else {
            $this->classRegister = new RegisterPreAuthDo();
        }
    }

    public function create(Invoice $model, array $post = [])
    {
        $post['orderNumber'] = $model->data['uniqid'];
        $post['amount'] = $model->sum * 100;
        $post['returnUrl'] = Url::to($this->returnUrl, true);
        $post['sessionTimeoutSecs'] = $this->sessionTimeoutSecs;
        if (array_key_exists('comment', $model->data)) {
            $post['description'] = $model->data['comment'];
        }
        if (array_key_exists('email', $model->data)) {
            $post['email'] = $model->data['email'];
        }
        $result = $this->send($this->classRegister->getActionRegister(), $post);
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
    public function send($action, $data)
    {
        $data = $this->insertAuthData($data);
        $url = ($this->testServer ? $this->urlTest : $this->url) . $action;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $out = curl_exec($curl);
        curl_close($curl);
        return Json::decode($out);
    }

    protected function insertAuthData(array $data)
    {
        $data['userName'] = $this->login;
        $data['password'] = $this->password;
        return $data;
    }
}
