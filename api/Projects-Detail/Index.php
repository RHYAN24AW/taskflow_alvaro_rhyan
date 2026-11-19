<?php

require_once __DIR__ . '/../../src/helpers/response.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/models/Project.php';

verificar_autenticacao();

$userId = (int) $_SESSION['user_id'];
$projects = new Project($pdo);
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
       
        $projeto = $projects->buscarPorId($id, $userId);

        
        if (!$projeto) {
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

       
        $nome = trim($dados['nome']);
        $descricao = trim($dados['descricao']);
        $status = trim($dados['status']);
        $erros = [];

        if (empty($nome)) {
            $erros['nome'] = 'nome: projeto deve conter um nome';
        }

        if (empty($status)) {
            $erros['status'] = 'status: defina o status do projeto';
        }

        if ($erros) {
            json_response(false, 'Dados inválidos', $erros, 422);
        }

       
        $projeto = $projects->atualizar($id, $userId, $nome, $descricao, $status);

      
        if (!$projeto) {
            json_response(false, 'Falha ao atualizar projeto.', null, 404);
        }

        json_response(true, 'Projeto atualizado com sucesso!', $projeto);

    } else {
      
        $excluido = $projects->deletar($id, $userId);

        if (!$excluido) {
            json_response(false, 'Projeto não encontrado.', null, 404);
        }

        json_response(true, 'Projeto excluído com sucesso!');
    }
} catch (PDOException | RuntimeException $e) {
    
    if ($e instanceof PDOException && $e->getCode() === '23503') {
      
        json_response(false, 'O projeto possui tarefas', null, 409);
    }

    error_log($e->getMessage());
    json_response(false, 'Erro interno do servidor', null, 500);
}