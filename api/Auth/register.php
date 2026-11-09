<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/validate.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(false, 'Método não permitido. Use POST.', null, 405);
}

$dados = json_decode(file_get_contents('php://input'), true);

if (!is_array($dados)) {
    json_response(false, 'Envie os dados em formato JSON.', null, 400);
}

try {
    $erros = validar_cadastro($dados);
    if ($erros) {
        json_response(false, 'Dados inválidos', $erros, 422);
    }

    $nome = trim($dados['nome']);
    $email = strtolower(trim($dados['email'])); // mesmo formato usado no login
    $senha = $dados['senha'];

    $consulta = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $consulta->execute(['email' => $email]);
    if ($consulta->fetch()) {
        json_response(false, 'Este e-mail já está cadastrado.', null, 409);
    }

    // Só o hash vai para o banco; a senha original nunca é gravada.
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $insert = $pdo->prepare(
        'INSERT INTO users (nome, email, senha_hash)
         VALUES (:nome, :email, :senha_hash)
         RETURNING id, nome, email, created_at'
    );
    $insert->execute([
        'nome' => $nome,
        'email' => $email,
        'senha_hash' => $senhaHash,
    ]);

    $usuario = $insert->fetch(PDO::FETCH_ASSOC);

    json_response(true, 'Usuário cadastrado com sucesso!', $usuario, 201);
} catch (PDOException $e) {
    // 23505 = violação de UNIQUE (e-mail cadastrado em paralelo)
    if ($e->getCode() === '23505') {
        json_response(false, 'Este e-mail já está cadastrado.', null, 409);
    }
    error_log('Erro no cadastro: ' . $e->getMessage());
    json_response(false, 'Erro interno do servidor', null, 500);
}
