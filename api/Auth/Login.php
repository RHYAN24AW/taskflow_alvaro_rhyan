<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/validate.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(false, 'Método não permitido. Use POST.', null, 405);
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    json_response(false, 'Envie os dados em formato JSON.', null, 400);
}

try {
    $erros = validar_login($dados);
    if ($erros) {
        json_response(false, 'Dados inválidos', $erros, 422);
    }

    $email = strtolower(trim($dados['email']));
    $senha = $dados['senha'];

    $stmt = $pdo->prepare(
        'SELECT id, nome, email, senha_hash FROM users WHERE email = :email'
    );
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mesma mensagem para e-mail inexistente e senha incorreta.
    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        json_response(false, 'Credenciais inválidas.', null, 401);
    }

    // Só depois do password_verify a sessão ganha o crachá.
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];

    json_response(true, 'Login realizado com sucesso.', [
        'id' => (int) $usuario['id'],
        'nome' => $usuario['nome'],
        'email' => $usuario['email'],
    ]);
} catch (PDOException $e) {
    error_log('Erro no login: ' . $e->getMessage());
    json_response(false, 'Erro interno do servidor', null, 500);
}
