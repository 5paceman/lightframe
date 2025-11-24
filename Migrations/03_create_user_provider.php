<?php

use App\Database\Migration;

class create_session_table extends Migration {

    public function migrate()
    {
        $this->table->name('user_providers');
        $this->table->increments('id');
        $this->table->foreignId('user_id')->on('id')->references('users');
        $this->table->string('provider', 50);
        $this->table->string('provider_id', 255);
        $this->table->timestamps();
    }

}

?>