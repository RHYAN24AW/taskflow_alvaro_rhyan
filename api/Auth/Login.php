<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/validate.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 
    json_response(false, 'Método inválido.', null, 405);
}

$dados = json_decode(file_get_contents('php://input'), true);

try {
    
    $erros = validar_login($dados);

    if ($erros) {
        json_response(false, 'Dados inválidos.', $erros, 422);
    }

    $email = trim($dados['email']);
    $senha = $dados['senha'];

   
    $stmt = $pdo->prepare(
        'SELECT id, nome, email, senha_hash FROM users WHERE email = :email'
    );
    $stmt->execute(['email' => $email]);

    $user = $stmt->fetch();

    
    $senhaCorreta = password_verify($senha, $user['senha_hash']);

    if (!$user || !$senhaCorreta){
        json_response(false, "Email ou senha inválidos.", null, 401);
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nome'] = $user['nome'];

    
    json_response(true, 'Login realizado com sucesso.', [
            'id' => (int) $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email']
        ]);

} catch (PDOException $e) {
  
    json_response(false, 'Erro interno do servidor.', null, 500);
}