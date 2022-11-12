<?php

$klein->respond('/ui/assets/bootstrap.min.css', function ($request, $response) {
    $response->header('Content-Type', 'text/css');
    $response->body(file_get_contents(__DIR__ . '/../vendor/twbs/bootstrap/dist/css/bootstrap.min.css'));
});

require_once(__DIR__ . '/setup/router.php');
require_once(__DIR__ . '/admin/router.php');
