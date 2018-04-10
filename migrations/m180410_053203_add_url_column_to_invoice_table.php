<?php

use yii\db\Migration;

/**
 * Handles adding url to table `invoice`.
 */
class m180410_053203_add_url_column_to_invoice_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('invoice', 'url', $this->string()->null());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('invoice', 'url');
    }
}
