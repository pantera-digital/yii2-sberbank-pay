<?php

namespace pantera\yii2\pay\sberbank;

use Closure;

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
}