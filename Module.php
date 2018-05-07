<?php

namespace pantera\yii2\pay\sberbank;

use Closure;
use pantera\yii2\pay\sberbank\components\Sberbank;
use yii\base\InvalidConfigException;

/**
 * Class Module
 * @package pantera\yii2\pay\sberbank
 *
 * @property Sberbank sberbank
 */
class Module extends \yii\base\Module
{
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
    /* @var Closure|null Колбек для генерации уникально идентификатора заказа */
    public $idGenerator;

    public function init()
    {
        parent::init();
        if (empty($this->successUrl)
            || empty($this->failUrl)) {
            throw new InvalidConfigException('Модуль настроен не правильно пожалуйсто прочтите документацию');
        }
    }
}