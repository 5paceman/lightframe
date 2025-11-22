<?php

class create_test_table extends Migration {

    public function migrate()
    {
        $this->table->name('test')
                ->increments('id')
                ->text('name')
                ->text('surname')
                ->boolean('enabled')
                ->timestamp('created')
                ->timestamp('updated');
    }

}

?>