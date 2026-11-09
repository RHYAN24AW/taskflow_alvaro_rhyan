<?php

require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificar_autenticacao();

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo !== 'GET') {
    header('Allow: GET');
    json_response(false, 'Método não permitido', null, 405);
}

try {
    // Nunca seleciona senha_hash.
    $sql = 'SELECT id, nome, email, created_at FROM users ORDER BY id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response(true, 'Usuários listados com sucesso', $usuarios);
} catch (PDOException $e) {
    error_log($e->getMessage());
    json_response(false, 'Erro interno ao consultar usuários', null, 500);
}
