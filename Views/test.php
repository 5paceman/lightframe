<?php

use App\Database\Database;

Database::get()->query()->table('test')->insert([
    'name' => 'username',
    'surname' => 'surname',
    'enabled' => rand(0, 1) ? 'true' : 'false'
]);

$results = Database::get()->query()->table('test')->select()->get();

?>

<html>
    <body>
        HI
        <?php var_dump($results); ?>
    </body>
</html>