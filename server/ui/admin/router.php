<?php

function authAdmin($request, $response): bool
{
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        $response->header("WWW-Authenticate", "Basic");
        $response->code(401);
        return false;
    } else {
        $searchUser = DB::queryFirstRow('SELECT passhash FROM user WHERE username=%s AND permission=0', $_SERVER['PHP_AUTH_USER']);
        if ($searchUser === null || password_verify($_SERVER['PHP_AUTH_PW'], $searchUser['passhash']) !== true) {
            $response->header("WWW-Authenticate", "Basic");
            $response->code(401);
            return false;
        } else
            return true;
    }

    return false;
}

$klein->respond('/admin', function ($request, $response) {
    $response->redirect('/admin/', 301);
});

$klein->respond('/admin/', function ($request, $response, $service) {
    if (!authAdmin($request, $response))
        return;

    $service->render(__DIR__ . '/index.php');
});

$klein->respond('GET', '/admin/library', function ($request, $response, $service) {
    if (!authAdmin($request, $response))
        return;

    $service->render(__DIR__ . '/library-list.php');
});


$klein->respond('POST', '/admin/library', function ($request, $response, $service) {
    if (!authAdmin($request, $response))
        return;

    if (!preg_match('/^([a-zA-Z0-9]|\-|\.|_| )+$/', $_POST['name'])) {
        $response->code('400');
        return 'Library name must be made by alphabet, number, space, `-`, `.`, and `_`.';
    }

    $_POST['basepath'] = realpath($_POST['basepath']);

    if (!is_dir($_POST['basepath'])) {
        $response->code('400');
        return 'Library path not exist in server.';
    }

    DB::insert('library', [
        'name' => $_POST['name'],
        'basepath' => $_POST['basepath']
    ]);

    $response->redirect('/admin/library/' . DB::insertId() . '/', 303);

    if (isset($_POST['allowguest']) && $_POST['allowguest'] === '1') {
        DB::insert('accessList', [
            'userid' => null, # Everyone
            'libraryId' => DB::insertId(),
            'permission' => 5, # Read/Play
        ]);
    }
});

$klein->respond('GET', '/admin/library/[i:id]/', function ($request, $response, $service) {
    if (!authAdmin($request, $response))
        return;

    $service->id = $request->id;

    $service->render(__DIR__ . '/library.php');
});

$klein->respond('DELETE', '/admin/library/[i:id]/', function ($request, $response, $service) {
    if (!authAdmin($request, $response))
        return;

    DB::delete('accessList', 'libraryId=%i', $request->id);
    DB::delete('library', 'id=%i', $request->id);

    $response->redirect('/admin/library', 303);
});

$klein->respond('DELETE', '/admin/library/[i:id]/acl/[i:aclId]', function ($request, $response) {
    if (!authAdmin($request, $response))
        return;

    DB::delete('accessList', 'id=%i AND libraryId=%i', $request->aclId, $request->id);

    $response->redirect('/admin/library/' . $request->id . '/', 303);
});

$klein->respond('POST', '/admin/library/[i:id]/acl', function ($request, $response) {
    if (!authAdmin($request, $response))
        return;

    $targetUser = -1;

    if ($_POST['userid'] === 'NULL')
        $targetUser = null;
    else if (is_numeric($_POST['userid']))
        $targetUser = intval($_POST['userid']);
    else {
        $response->code('400');
        return 'Invalid user selected';
    }

    $permission = 0;

    if (isset($_POST['read']) && $_POST['read'] === '1')
        $permission += 0b100;
    if (isset($_POST['write']) && $_POST['write'] === '1')
        $permission += 0b010;
    if (isset($_POST['play']) && $_POST['play'] === '1')
        $permission += 0b001;

    if ($permission < 1) {
        $response->code('400');
        return 'Invalid permission';
    }

    DB::insert('accessList', [
        'userid' => $targetUser,
        'libraryId' => $request->id,
        'permission' => $permission
    ]);

    $response->redirect('/admin/library/' . $request->id . '/', 303);
});
