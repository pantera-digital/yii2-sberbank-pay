<?php

use yii\db\Migration;

/**
 * Handles adding remote_id to table `invoice`.
 */
class m180331_054941_add_remote_id_column_to_invoice_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('invoice', 'remote_id', $this->integer()->null());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('invoice', 'remote_id');
    }
}
