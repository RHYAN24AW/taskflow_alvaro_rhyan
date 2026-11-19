<?php

function validar_cadastro(array $dados): array
{
    $erros = [];

   
    $nome = trim($dados['nome'] ?? '');
    if (empty($nome)) {
        $erros['nome'] = 'Nome é obrigatório.';
    }

   
    $email = trim($dados['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido.';
    }

    
    $senha = strlen($dados['senha']);
    if (empty($senha)) {
        $erros['senha'] = 'A senha é obrigatória.';
    }
    if ($senha < 6) {
        $erros['senha'] = 'A senha deve ter no mínimo 6 caractéres.';
    }
    return $erros;
}

function validar_login(array $dados): array
{
    $erros = [];
    
    $email = trim($dados['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido.';
    }

   
    $senha = strlen($dados['senha']);
    if (empty($senha) || $senha < 6) {
        $erros['senha'] = 'A senha deve ter no mínimo 6 caractéres.';
    }

    return $erros;
}

function ler_json_corpo(): ?array
{
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados) || array_is_list($dados)) {
        return null;
    }
    return $dados;
}

function validar_data_iso(string $data): bool
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    return $d !== false && $d->format('Y-m-d') === $data;
}


function comprimento_texto(string $texto): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }
    $quantidade = preg_match_all('/./us', $texto);
    return $quantidade === false ? strlen($texto) : $quantidade;
}
