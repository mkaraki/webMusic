<?php

$klein->respond('/ui/assets/bootstrap.min.css', function ($request, $response) {
    $response->file(__DIR__ . '/../vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
});

require_once(__DIR__ . '/setup/router.php');
