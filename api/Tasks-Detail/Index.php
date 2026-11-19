<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../src/helpers/validate.php';
require_once __DIR__ . '/../../src/models/Task.php';

verificar_autenticacao();
$userId = (int) $_SESSION['user_id'];
$model = new Task($pdo);
$metodo = $_SERVER['REQUEST_METHOD'];

$permitidos = ['GET', 'PUT', 'PATCH', 'DELETE'];
if (!in_array($metodo, $permitidos, true)) {
    header('Allow: ' . implode(', ', $permitidos));
    json_response(false, 'Método não permitido', null, 405);
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
if ($id === false) {
    json_response(false, 'id inválido', null, 400);
}

try {
    if ($metodo === 'GET') {
        $tarefa = $model->buscarPorId($id, $userId);
        if ($tarefa === null) {
            json_response(false, 'Tarefa não encontrada', null, 404);
        }
        json_response(true, 'Tarefa encontrada', $tarefa, 200);

    } elseif ($metodo === 'PATCH') {
        $dados = ler_json_corpo();
        if ($dados === null) {
            json_response(false, 'Corpo JSON inválido', null, 400);
        }
        $status = $dados['status'] ?? null;
        if (!is_string($status) || !in_array($status, Task::STATUS, true)) {
            json_response(false, 'status inválido', null, 400);
        }
        $tarefa = $model->atualizarStatus($id, $userId, $status);
        if ($tarefa === null) {
            json_response(false, 'Tarefa não encontrada', null, 404);
        }
        json_response(true, 'Status da tarefa atualizado', $tarefa, 200);

    } elseif ($metodo === 'PUT') {
        $dados = ler_json_corpo();
        if ($dados === null) {
            json_response(false, 'Corpo JSON inválido', null, 400);
        }

        $titulo = $dados['titulo'] ?? null;
        $descricao = $dados['descricao'] ?? null;
        $status = $dados['status'] ?? null;
        $prioridade = $dados['prioridade'] ?? null;
        $dataInicio = $dados['data_inicio'] ?? null;
        $dataPrazo = $dados['data_prazo'] ?? null;
        $erros = [];

        if (!is_string($titulo) || comprimento_texto(trim($titulo)) < 1 || comprimento_texto(trim($titulo)) > 200) {
            $erros['titulo'] = 'titulo deve conter de 1 a 200 caracteres.';
        }
        if ($descricao !== null && !is_string($descricao)) {
            $erros['descricao'] = 'descricao deve ser texto ou null.';
        }
        if (!is_string($status) || !in_array($status, Task::STATUS, true)) {
            $erros['status'] = 'status inválido.';
        }
        if (!is_string($prioridade) || !in_array($prioridade, Task::PRIORIDADES, true)) {
            $erros['prioridade'] = 'prioridade inválida.';
        }
        if ($dataInicio !== null && (!is_string($dataInicio) || !validar_data_iso($dataInicio))) {
            $erros['data_inicio'] = 'data_inicio inválida.';
        }
        if ($dataPrazo !== null && (!is_string($dataPrazo) || !validar_data_iso($dataPrazo))) {
            $erros['data_prazo'] = 'data_prazo inválida.';
        }
        if (is_string($dataInicio) && is_string($dataPrazo)
            && validar_data_iso($dataInicio) && validar_data_iso($dataPrazo)
            && $dataPrazo < $dataInicio) {
            $erros['data_prazo'] = 'data_prazo não pode ser anterior a data_inicio.';
        }
        if ($erros !== []) {
            json_response(false, 'Dados inválidos', $erros, 400);
        }

        $tarefa = $model->atualizar(
            $id, $userId, trim($titulo),
            $descricao === null ? null : trim($descricao),
            $status, $prioridade, $dataInicio, $dataPrazo
        );
        if ($tarefa === null) {
            json_response(false, 'Tarefa não encontrada', null, 404);
        }
        json_response(true, 'Tarefa atualizada', $tarefa, 200);

    } else {
        $excluido = $model->deletar($id, $userId);
        if (!$excluido) {
            json_response(false, 'Tarefa não encontrada', null, 404);
        }
        json_response(true, 'Tarefa excluída', ['id' => $id], 200);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    json_response(false, 'Erro interno do servidor', null, 500);
}
