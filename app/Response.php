<?php

function view(string $template, array $data = [])
{
    $file = __DIR__.'/../views/'.$template.".php";

    if(!file_exists($file)) {
        throw new Exception("Template not found: $file");
    }

    extract($data);
    ob_start();
    include $file;
    echo ob_get_clean();
}

function json($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
}

?>