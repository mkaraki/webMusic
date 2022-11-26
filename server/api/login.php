<?php

function loginAndExtendTokenExpire(string $token): null|int
{
    $tokenSession = null;

    if (apcu_exists('token/' . $token))
        $tokenSession = apcu_fetch('token/' . $token);
        
    if ($tokenSession === null) {
        $tokenSession = DB::queryFirstRow('SELECT userid, expire FROM sessionToken WHERE token=%s AND expire>CURRENT_TIMESTAMP', $token);

        if ($tokenSession === null)
            return null;

        $tokenSession['expire'] = intval((new DateTime($tokenSession['expire']))->format('U'));
        apcu_store('token/' . $token, $tokenSession, $tokenSession['expire'] - time());
    }

    // if token expire is less than 3 days
    if ($tokenSession['expire'] - 259200 < time()) {
        DB::update('sessionToken', ['expire' => new DateTime('+1 weeks')], 'token=%s', $token);
        $tokenSession['expire'] = time() + 604800;
        apcu_store('token/' . $token, $tokenSession, $tokenSession['expire'] - time());
    }

    if (!is_numeric($tokenSession['userid']))
        return null;

    return intval($tokenSession['userid']);
}

function loginAndExtendTokenExpireWithKlein($request, $response): null|int
{
    if ($request->cookies() === null) {
        $response->code(401);
        return null;
    }

    if (!isset($request->cookies()['auth'])) {
        $response->code(401);
        return null;
    }

    $loggedUser = loginAndExtendTokenExpire($request->cookies()['auth']);
    if ($loggedUser === null) {
        $response->code(401);
        return null;
    } else
        return $loggedUser;
}

$klein->respond('POST', '/login', function ($request, $response) {
    $uname = $request->paramsPost()['username'] ?? '';
    $pass  = $request->paramsPost()['password'] ?? '';

    $dbuser = DB::queryFirstRow('SELECT id, passhash FROM user WHERE username=%s', $uname);
    if ($dbuser === null) {
        $response->code(401);
        return;
    }

    if (password_verify($pass, $dbuser['passhash']) !== true) {
        $response->code(401);
        return;
    }

    // Got 64 chars token
    $token = base_convert(openssl_random_pseudo_bytes(65535), 2, 36);

    DB::insert('sessionToken', [
        'token' => $token,
        'userid' => $dbuser['id'],
        'expire' => new DateTime('+1 weeks')
    ]);

    setCors($request, $response);
    setcookie("auth", $token, [
        'expires' => time() + 604800 /* one week */,
        'samesite' => 'None',
        'secure' => true,
    ]);
    $response->json(['token' => $token]);
});

$klein->respond('GET', '/login/check', function ($request, $response) {
    setCors($request, $response);
    $user = loginAndExtendTokenExpireWithKlein($request, $response);

    if ($user !== null)
        $response->json(['loggedUser' => $user]);
});
