<?php

class create_test_table extends Migration {

    public function migrate()
    {
        $this->table->name('users');
        $this->table->increments('id');
        $this->table->text('email');
        $this->table->text('password');
        $this->table->timestamp('last_login');
        $this->table->timestamps();
    }

}

?>