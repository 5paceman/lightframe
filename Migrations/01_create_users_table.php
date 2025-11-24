<?php

use App\Database\Migration;

class create_users_table extends Migration {

    public function migrate()
    {
        $this->table->name('users');
        $this->table->increments('id');
        $this->table->text('email');
        $this->table->text('password')->nullable();
        $this->table->timestamp('last_login');
        $this->table->timestamps();
    }

}

?>