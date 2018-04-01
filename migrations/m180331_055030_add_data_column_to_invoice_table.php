<?php

use yii\db\Migration;

/**
 * Handles adding data to table `invoice`.
 */
class m180331_055030_add_data_column_to_invoice_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('invoice', 'data', 'JSON NULL DEFAULT NULL');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('invoice', 'data');
    }
}
