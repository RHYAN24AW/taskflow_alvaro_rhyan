<?php

require_once __DIR__ . '/../../src/helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(false, 'Método não permitido. Use POST.', null, 405);
}

session_start();

// Esvazia o array da sessão (devolve o crachá).
$_SESSION = [];

// Apaga o cookie de sessão no navegador/Postman.
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $p['path'],
        'domain' => $p['domain'],
        'secure' => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'] ?: 'Lax',
    ]);
}

// Destrói os dados da sessão no servidor.
session_destroy();

json_response(true, 'Logout realizado com sucesso.');
