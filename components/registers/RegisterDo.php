<?php

namespace pantera\yii2\pay\sberbank\components\registers;

use yii\db\Expression;

class RegisterDo implements RegisterInterface
{
    public function getActionRegister()
    {
        return 'register.do';
    }

    public function successStatus()
    {
        return 2;
    }

    public function getDataForUpdate()
    {
        return [
            'status' => 'S',
            'pay_time' => new Expression("NOW()"),
        ];
    }
}