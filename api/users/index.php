<?php

require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../config/database.php';

verificar_autenticacao();

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo !== 'GET') {
    json_response(false, 'Método não permitido', null, 405);
}

try {
    
    $sql = "SELECT id, nome, email FROM users";
    $stmt = $pdo->prepare($sql);

   
    $stmt->execute();
    
    $usuarios = $stmt->fetchAll();
 
    json_response(true, 'Usuários encontrados', $usuarios, 200);
} catch (PDOException $e) {
   
    json_response(false, 'Erro ao buscar usuários', null, 500);
}