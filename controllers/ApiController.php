<?php
/**
 * Created by PhpStorm.
 * User: singletonn
 * Date: 3/31/18
 * Time: 3:43 PM
 */

namespace pantera\yii2\pay\sberbank\controllers;


use ErrorException;
use Exception;
use pantera\yii2\pay\sberbank\models\Invoice;
use Yii;
use yii\base\InvalidParamException;
use yii\rest\Controller;

class ApiController extends Controller
{
    use OrderTrait;

    public function verbs()
    {
        return [
            'remote-create' => ['POST'],
        ];
    }

    public function actionCreate()
    {
        if (is_null(Yii::$app->request->post('id')) || is_null(Yii::$app->request->post('price'))) {
            throw new InvalidParamException('Обязательно должны присутствовать идентификатор и цена');
        }
        $data = [
            'phone' => Yii::$app->request->post('phone'),
            'mail' => Yii::$app->request->post('mail'),
            'comment' => Yii::$app->request->post('comment'),
        ];
        $model = Invoice::addSberbank(
            null,
            Yii::$app->request->post('price'),
            Yii::$app->request->post('id'),
            $data
        );
        try {
            $result = $this->createOrder($model);
            if (array_key_exists('errorCode', $result)) {
                throw new ErrorException($result['errorMessage']);
            }
            $orderId = $result['orderId'];
            $formUrl = $result['formUrl'];
            $model->orderId = $orderId;
            $model->update();
            if (is_null($this->module->apiCallbackCreateSuccess)) {
                return [
                    'url' => $formUrl,
                ];
            } else {
                return call_user_func($this->module->apiCallbackCreateSuccess, $result, $model);
            }
        } catch (Exception $e) {
            if (is_null($this->module->apiCallbackCreateFail)) {
                return $e;
            } else {
                return call_user_func($this->module->apiCallbackCreateFail, $e, $model);
            }
        }
    }
}