<?php

namespace pantera\yii2\pay\sberbank;

use Closure;
use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    /**
     * @var string Логин в сбербанке
     */
    public $login;
    /**
     * @var string Пароль в сбербанке
     */
    public $password;
    /**
     * @var string Url адрес страницы успешной оплаты
     */
    public $successUrl;
    /**
     * @var string Url адрес страницы если оплата провалилась
     */
    public $failUrl;
    /**
     * @var null|Closure Callback при успешной оплате
     */
    public $successCallback = null;
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
    /**
     * @var string Url адрес страницы для возврата с платежного шлюза
     * необходимо указывать без host
     */
    public $returnUrl = '/sberbank/default/complete';
    /* @var null|Closure Калбэк вызывается в случии успешно создания заказа по api */
    public $apiCallbackCreateSuccess;
    /* @var null|Closure Калбэк вызывается в случии ошибки при создания заказа по api */
    public $apiCallbackCreateFail;
    /* @var int Время жизни заказа в секундах */
    public $sessionsTimeoutSecs = 1200;
    /* @var Closure|null Колбек для генерации уникально идентификатора заказа */
    public $idGenerator;

    public function init()
    {
        parent::init();
        if (empty($this->login)
            || empty($this->password)
            || empty($this->successUrl)
            || empty($this->failUrl)
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
}