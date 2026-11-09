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

if (!in_array($metodo, ['GET', 'PUT', 'DELETE'], true)) {
    header('Allow: GET, PUT, DELETE');
    json_response(false, 'Método não permitido', null, 405);
}

$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
if ($id === false) {
    json_response(false, 'id inválido ou ausente', null, 400);
}

try {
    if ($metodo === 'GET') {
        $projeto = $model->buscarPorId($id, $userId);
        if ($projeto === null) {
            json_response(false, 'Projeto não encontrado', null, 404);
        }
        json_response(true, 'Projeto encontrado', $projeto);
    } elseif ($metodo === 'PUT') {
        $corpo = file_get_contents('php://input');

        try {
            $dados = json_decode($corpo, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            json_response(false, 'JSON inválido', null, 400);
        }

        if (!is_array($dados)) {
            json_response(false, 'O corpo deve ser um objeto JSON', null, 400);
        }

        $resultado = validar_projeto($dados, true);
        if ($resultado['erros']) {
            json_response(false, 'Dados inválidos', $resultado['erros'], 422);
        }

        // user_id do corpo é ignorado: o dono nunca muda.
        $projeto = $model->atualizar(
            $id,
            $userId,
            $resultado['dados']['nome'],
            $resultado['dados']['descricao'],
            $resultado['dados']['status']
        );
        if ($projeto === null) {
            json_response(false, 'Projeto não encontrado', null, 404);
        }
        json_response(true, 'Projeto atualizado com sucesso', $projeto);
    } else {
        if (!$model->deletar($id, $userId)) {
            json_response(false, 'Projeto não encontrado', null, 404);
        }
        json_response(true, 'Projeto excluído com sucesso', ['id' => $id]);
    }
} catch (PDOException | RuntimeException $e) {
    if ($e instanceof PDOException && $e->getCode() === '23503') {
        json_response(
            false,
            'Não é possível excluir: o projeto possui tarefas.',
            null,
            409
        );
    }
    error_log($e->getMessage());
    json_response(false, 'Erro interno do servidor', null, 500);
}
