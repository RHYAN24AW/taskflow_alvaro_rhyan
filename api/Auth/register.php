<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/validate.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    
    json_response(false, 'Método inválido.', null, 405);
}

$dados = json_decode(file_get_contents('php://input'), true);

try {
    
    $erros = validar_cadastro($dados);

    if ($erros) {
        json_response(false, 'Dados inválidos.', $erros, 422);
    }

    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $senha = $dados['senha'];

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        json_response(false, 'E-mail já cadastrado.', null, 409);
    }

   
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

 
    $stmt = $pdo->prepare(
        'INSERT INTO users (nome, email, senha_hash)
         VALUES (:nome, :email, :senha_hash)
         RETURNING id'
    );
    $stmt->execute([
        ':nome'       => $nome,
        ':email'      => $email,
        ':senha_hash' => $senha_hash,
    ]);
    $id = (int) $stmt->fetchColumn();

    
    json_response(true, 'Usuário cadastrado com sucesso.', [
        'id'    => $id,
        'nome'  => $nome,
        'email' => $email,
    ], 201);

} catch (PDOException $e) {
   
    json_response(false, 'Erro interno do servidor.', null, 500);
}