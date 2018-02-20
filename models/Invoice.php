<?php

namespace pantera\yii2\pay\sberbank\models;
use Yii;

/**
 * This is the model class for table "invoice".
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property string $sum
 * @property int $status
 * @property string $created_at
 * @property string $pay_time
 * @property string $method
 * @property string $orderId
 */
class Invoice extends \yii\db\ActiveRecord
{
    /**
     * Добавление оплаты через сбербанк
     * @param integer $orderId Идентификатор заказа
     * @param float $price Цена заказа
     * @return self
     */
    public static function addSberbank($orderId, $price)
    {
        $model = new self();
        $model->order_id = $orderId;
        $model->user_id = Yii::$app->user->id;
        $model->method = 'SB';
        $model->sum = $price;
        $model->status = 'I';
        $model->save();
        return $model;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id'], 'integer'],
            [['order_id', 'sum', 'method', 'status'], 'required'],
            [['sum'], 'number'],
            [['created_at', 'pay_time', 'orderId'], 'safe'],
            [['method'], 'string', 'max' => 7],
            [['status'], 'string', 'max' => 7],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'sum' => 'Sum',
            'status' => 'Status',
            'created_at' => 'Created At',
            'pay_time' => 'Pay Time',
            'method' => 'Method',
            'orderId' => 'orderId',
        ];
    }
}
