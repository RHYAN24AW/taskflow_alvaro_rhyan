<?php

require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../src/helpers/validate.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/models/Project.php';

verificar_autenticacao();

$userId = (int) $_SESSION['usuario_id'];
$model = new Project($pdo);
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($metodo === 'GET') {
        $projetos = $model->listar($userId);
        json_response(true, 'Projetos listados com sucesso', $projetos);
    } elseif ($metodo === 'POST') {
        $corpo = file_get_contents('php://input');

        try {
            $dados = json_decode($corpo, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            json_response(false, 'JSON inválido', null, 400);
        }

        if (!is_array($dados)) {
            json_response(false, 'O corpo deve ser um objeto JSON', null, 400);
        }

        $resultado = validar_projeto($dados);
        if ($resultado['erros']) {
            json_response(false, 'Dados inválidos', $resultado['erros'], 422);
        }

        // O dono vem da sessão; um "user_id" no JSON é ignorado.
        $projeto = $model->criar(
            $resultado['dados']['nome'],
            $resultado['dados']['descricao'],
            $userId
        );
        json_response(true, 'Projeto criado com sucesso', $projeto, 201);
    } else {
        header('Allow: GET, POST');
        json_response(false, 'Método não permitido', null, 405);
    }
} catch (PDOException | RuntimeException $e) {
    error_log($e->getMessage()); // o detalhe fica no servidor
    json_response(false, 'Erro interno do servidor', null, 500);
}
