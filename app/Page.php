<?php

function view(string $template, array $data = [])
{
    $file = __DIR__.'/../pages/'.$template.".php";

    if(!file_exists($file)) {
        throw new Exception("Template not found: $file");
    }

    extract($data);
    ob_start();
    include $file;
    return ob_get_clean();
}

?>