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

try {
    if ($metodo === 'GET') {
        $filtros = [];

        if (isset($_GET['project_id'])) {
            $projectId = filter_var($_GET['project_id'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1]
            ]);
            if ($projectId === false) {
                json_response(false, 'project_id inválido', null, 400);
            }
            $filtros['project_id'] = $projectId;
        }

        if (isset($_GET['status'])) {
            if (!is_string($_GET['status']) || !in_array($_GET['status'], Task::STATUS, true)) {
                json_response(false, 'status inválido', null, 400);
            }
            $filtros['status'] = $_GET['status'];
        }

        if (isset($_GET['prioridade'])) {
            if (!is_string($_GET['prioridade']) || !in_array($_GET['prioridade'], Task::PRIORIDADES, true)) {
                json_response(false, 'prioridade inválida', null, 400);
            }
            $filtros['prioridade'] = $_GET['prioridade'];
        }

        if (isset($_GET['limite'])) {
            $limite = filter_var($_GET['limite'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 100]
            ]);
            if ($limite === false) {
                json_response(false, 'limite inválido', null, 400);
            }
            $filtros['limite'] = $limite;
        }

        json_response(true, 'Tarefas listadas', $model->listar($userId, $filtros), 200);

    } elseif ($metodo === 'POST') {
        $dados = ler_json_corpo();
        if ($dados === null) {
            json_response(false, 'Corpo JSON inválido', null, 400);
        }

        $projectId = filter_var($dados['project_id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);
        $titulo = $dados['titulo'] ?? null;
        $descricao = $dados['descricao'] ?? null;
        $prioridade = $dados['prioridade'] ?? 'media';
        $dataInicio = $dados['data_inicio'] ?? null;
        $dataPrazo = $dados['data_prazo'] ?? null;
        $erros = [];

        if ($projectId === false) {
            $erros['project_id'] = 'project_id deve ser um inteiro positivo.';
        }
        if (!is_string($titulo) || comprimento_texto(trim($titulo)) < 1 || comprimento_texto(trim($titulo)) > 200) {
            $erros['titulo'] = 'titulo deve conter de 1 a 200 caracteres.';
        }
        if ($descricao !== null && !is_string($descricao)) {
            $erros['descricao'] = 'descricao deve ser texto ou null.';
        }
        if (!is_string($prioridade) || !in_array($prioridade, Task::PRIORIDADES, true)) {
            $erros['prioridade'] = 'prioridade inválida.';
        }
        if ($dataInicio !== null && (!is_string($dataInicio) || !validar_data_iso($dataInicio))) {
            $erros['data_inicio'] = 'data_inicio deve ser uma data válida no formato AAAA-MM-DD ou null.';
        }
        if ($dataPrazo !== null && (!is_string($dataPrazo) || !validar_data_iso($dataPrazo))) {
            $erros['data_prazo'] = 'data_prazo deve ser uma data válida no formato AAAA-MM-DD ou null.';
        }
        if (is_string($dataInicio) && is_string($dataPrazo)
            && validar_data_iso($dataInicio) && validar_data_iso($dataPrazo)
            && $dataPrazo < $dataInicio) {
            $erros['data_prazo'] = 'data_prazo não pode ser anterior a data_inicio.';
        }

        if ($erros !== []) {
            json_response(false, 'Dados inválidos', $erros, 400);
        }

        $tarefa = $model->criar(
            $projectId,
            trim($titulo),
            $descricao === null ? null : trim($descricao),
            $prioridade,
            $dataInicio,
            $dataPrazo,
            $userId
        );
        if ($tarefa === null) {
            json_response(false, 'Projeto não encontrado', null, 404);
        }
        json_response(true, 'Tarefa criada', $tarefa, 201);

    } else {
        header('Allow: GET, POST');
        json_response(false, 'Método não permitido', null, 405);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    json_response(false, 'Erro interno do servidor', null, 500);
}
