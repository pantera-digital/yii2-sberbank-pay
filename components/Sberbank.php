<?php

namespace ykweb\yii2\pay\sberbank\components;

use ykweb\yii2\pay\sberbank\models\Invoice;
use ykweb\yii2\pay\sberbank\Module;
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
    public $url = 'https://securepayments.sberbank.kz/payment/rest/';
    /**
     * @var string Тестовый адрес платежного шлюза
     */
    public $urlTest = 'https://3dsec.sberbank.kz/payment/rest/';
    /**
     * @var bool Если true будет использован тестовый сервер
     */
    public $testServer = true;
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

    public function create(Invoice $model, array $post = [])
    {
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
    public function send($action, $data)
    {
        $data = $this->insertAuthData($data);
        $url = ($this->testServer ? $this->urlTest : $this->url) . $action;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_VERBOSE => true,
            //CURLOPT_SSL_VERIFYHOST => false,
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data, '', '&'),
            CURLOPT_ENCODING, "gzip",
            CURLOPT_ENCODING, '',
        ));
        //$out = curl_exec($curl);

        $response = curl_exec($curl);
        $response = json_decode($response, true);

        curl_close($curl);
        return $response;
        //return Json::decode($out);
    }

    protected function insertAuthData(array $data)
    {
        $data['userName'] = $this->login;
        $data['password'] = $this->password;
        return $data;
    }
}
