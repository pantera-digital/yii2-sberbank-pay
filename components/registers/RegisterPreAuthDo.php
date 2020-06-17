<?php

namespace pantera\yii2\pay\sberbank\components\registers;

class RegisterPreAuthDo implements RegisterInterface
{
    public function getActionRegister()
    {
        return 'registerPreAuth.do';
    }

    public function successStatus()
    {
        return 1;
    }

    public function getDataForUpdate()
    {
        return [
            'status' => 'P',
        ];
    }
}