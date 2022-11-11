<?php

$klein->respond('GET', '/setup/createtable', function ($request, $response, $service) {
    $service->render(__DIR__ . '/createtable.php');
});

$klein->respond('POST', '/setup/createtable', function ($request, $response, $service) {
    $dbstruct = file_get_contents(__DIR__ . '/../../init.db/database.sql');
    $dbstruct = str_replace(["\r\n", "\n", "\r"], " ", $dbstruct);
    $dbstruct = preg_replace('/\s+/', ' ', $dbstruct);

    $sqls = explode(';', $dbstruct);

    foreach ($sqls as $sql) {
        if (empty($sql))
            continue;
        DB::query($sql);
    }

    $response->redirect('/setup/createuser', 303);
});


$klein->respond('GET', '/setup/createuser', function ($request, $response, $service) {
    if (DB::queryFirstRow('SELECT id FROM user LIMIT 1') !== null) {
        $response->code('404');
        return;
    }

    $service->render(__DIR__ . '/createuser.php');
});

$klein->respond('POST', '/setup/createuser', function ($request, $response, $service) {
    if (DB::queryFirstRow('SELECT id FROM user LIMIT 1') !== null) {
        $response->code('404');
        return;
    }

    $result = DB::insert('user', [
        'username' => $_POST['username'],
        'passhash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'permission' => 0 # Permission 0: Administrator
    ]);

    if ($result !== true) {
        $response->code(500);
        return 'error';
    } else {
        $response->redirect('/', 301);
    }
});
