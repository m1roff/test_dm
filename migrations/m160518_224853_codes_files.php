<?php

use yii\db\Migration;

class m160518_224853_codes_files extends Migration
{
    public function up()
    {
        $this->createTable('codes_files', [
                'id_codes_files' => 'pk',
                'fname'          => 'varchar(150) comment "Название файла"',
                'fname_hash'     => 'char(32) comment "Хэш имени, указанной в скрипте"',
                'f_last_hash'    => 'char(32) comment "Хэш для сравнения"',
                'unique key `fname_hash` (`fname_hash`)',
                'key `f_last_hash` (`f_last_hash`)',
            ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
    }

    public function down()
    {
        $this->dropTable('codes_files');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
