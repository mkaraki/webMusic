<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/_config.php';

$klein = new \Klein\Klein();

require_once(__DIR__ . '/api/login.php');

function setCors($request, $response, bool $auth = true)
{
    global $cors_origin;

    if (isset($cors_origin))
        $response->header('Access-Control-Allow-Origin', $cors_origin);
    if ($auth)
        $response->header('Access-Control-Allow-Credentials', 'true');
}

$klein->respond('/[|app:entry]', function ($request, $response) {
    try {
        $testUser = DB::queryFirstRow('SELECT id FROM user LIMIT 1');
        if ($testUser === null) {
            $response->redirect('/setup/createuser', 302);
            return;
        }
    } catch (\Throwable $ex) {
        $response->redirect('/setup/createtable', 302);
        return;
    }

    $response->redirect('/app/');
});

$klein->respond('GET', '/version', function ($request, $response) {
    $response->json(array(
        'api' => 2023_10_21_00002
    ));
});

require_once(__DIR__ . '/ui/router.php');

require_once(__DIR__ . '/api/library.php');
require_once(__DIR__ . '/api/history.php');

$klein->dispatch();
