<?php

use App\Database\Migration;

class create_session_table extends Migration {

    public function migrate()
    {
        $this->table->name('sessions');
        $this->table->increments('id');
        $this->table->string('session_id');
        $this->table->foreignId('user_id')->references('id')->on('users');
        $this->table->timestamps();

        $this->table->index(['session_id']);
    }

}

?>