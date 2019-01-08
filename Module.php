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
     * @var string url-адрес страницы успешной оплаты
     */
    public $successUrl;
    
    /**
     * @var string url-адрес страницы неуспешной оплаты
     */
    public $failUrl;
    
    /**
     * @var Closure|null Callback при успешной оплате
     */
    public $successCallback = null;
    
    /**
     * @var Closure|null Callback для генерации уникального идентификатора заказа
     */
    public $idGenerator = null;

    public function init()
    {
        parent::init();
        if (empty($this->successUrl)
            || empty($this->failUrl)) {
            throw new InvalidConfigException('Модуль настроен неправильно, пожалуйста прочтите документацию');
        }
    }
}
