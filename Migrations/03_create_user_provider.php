<?php

use App\Database\Migration;

class create_user_provider extends Migration {

    public function migrate()
    {
        $this->table->name('user_providers');
        $this->table->increments('id');
        $this->table->foreignId('user_id')->on('users')->references('id');
        $this->table->string('provider', 50);
        $this->table->string('provider_id', 255);
        $this->table->timestamps();
    }

}

?>