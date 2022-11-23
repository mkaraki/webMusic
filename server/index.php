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

$klein->respond('/app/', function ($request, $response) {
    $path = __DIR__ . '/public/index.html';
    if (!is_file($path)) {
        $response->code(404);
        return;
    }

    $content = file_get_contents($path);
    $response->body($content);
});

$klein->respond('/app/[**:path]', function ($request, $response) {
    $basepath = realpath(__DIR__ . '/public');
    $path = $basepath . '/' . $request->path;
    if (!str_starts_with(realpath($path), $basepath)) {
        $response->code(403);
        return;
    }
    if (!is_file($path)) {
        $response->code(404);
        return;
    }

    $content = file_get_contents($path);
    switch (end(explode('.', $path))) {
        case "js":
            $response->header('Content-Type', 'application/javascript');
            break;

        case "css":
            $response->header('Content-Type', 'text/css');
            break;

        default:
            $response->header('Content-Type', mime_content_type($path));
            break;
    }
    $response->body($content);
});

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
        'api' => 2022_11_23_00003
    ));
});

require_once(__DIR__ . '/ui/router.php');

require_once(__DIR__ . '/api/library.php');
require_once(__DIR__ . '/api/history.php');

$klein->dispatch();
