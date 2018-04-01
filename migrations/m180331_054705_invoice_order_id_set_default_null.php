<?php

use pantera\yii2\pay\sberbank\models\Invoice;
use yii\db\Migration;

/**
 * Class m180331_054705_invoice_order_id_set_default_null
 */
class m180331_054705_invoice_order_id_set_default_null extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn(Invoice::tableName(), 'order_id', $this->integer()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180331_054705_invoice_order_id_set_default_null cannot be reverted.\n";
        $this->alterColumn(Invoice::tableName(), 'order_id', $this->integer()->notNull());
        return true;
    }
}
