<?php

require_once __DIR__ . '/../../src/helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    json_response(false, 'Método inválido.', null, 405);
}

session_start();


$_SESSION = [];


if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?: 'Lax',
    ]);
}


session_destroy();


json_response(true, 'Logout realizado com sucesso.');