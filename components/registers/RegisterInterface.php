<?php

namespace pantera\yii2\pay\sberbank\components\registers;

interface RegisterInterface
{
    public function getActionRegister();

    public function successStatus();

    public function getDataForUpdate();
}
