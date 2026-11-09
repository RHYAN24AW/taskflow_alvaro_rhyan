<?php

/**
 * Cadastro: nome (obrigatório, até 100), e-mail válido (até 150)
 * e senha com no mínimo 8 caracteres. Tipos errados viram erro, não fatal.
 */
function validar_cadastro(array $dados): array
{
    $erros = [];

    $nome = $dados['nome'] ?? null;
    if (!is_string($nome) || trim($nome) === '') {
        $erros[] = 'nome: é obrigatório';
    } elseif (mb_strlen(trim($nome), 'UTF-8') > 100) {
        $erros[] = 'nome: deve ter no máximo 100 caracteres';
    }

    $email = $dados['email'] ?? null;
    if (!is_string($email) || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'email: informe um e-mail válido';
    } elseif (mb_strlen(trim($email), 'UTF-8') > 150) {
        $erros[] = 'email: deve ter no máximo 150 caracteres';
    }

    $senha = $dados['senha'] ?? null;
    if (!is_string($senha) || mb_strlen($senha, 'UTF-8') < 8) {
        $erros[] = 'senha: deve ter pelo menos 8 caracteres';
    }

    return $erros;
}

/** Login: só confere se e-mail e senha chegaram no formato certo. */
function validar_login(array $dados): array
{
    $erros = [];

    $email = $dados['email'] ?? null;
    if (!is_string($email) || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'email: informe um e-mail válido';
    }

    $senha = $dados['senha'] ?? null;
    if (!is_string($senha) || $senha === '') {
        $erros[] = 'senha: é obrigatória';
    }

    return $erros;
}

/**
 * Projetos (POST e PUT). Confere o tipo antes de trim()/mb_strlen().
 * Retorna ['erros' => [...], 'dados' => ['nome', 'descricao', 'status']].
 * O user_id nunca é lido do corpo: o dono vem da sessão.
 */
function validar_projeto(array $dados, bool $exigirStatus = false): array
{
    $erros = [];

    $nome = $dados['nome'] ?? null;
    if (!is_string($nome)) {
        $erros[] = 'nome: deve ser texto';
        $nome = null;
    } else {
        $nome = trim($nome);
        $tamanho = mb_strlen($nome, 'UTF-8');
        if ($tamanho < 1 || $tamanho > 150) {
            $erros[] = 'nome: deve ter de 1 a 150 caracteres';
        }
    }

    $descricao = $dados['descricao'] ?? null;
    if ($descricao !== null && !is_string($descricao)) {
        $erros[] = 'descricao: deve ser texto ou null';
        $descricao = null;
    } elseif (is_string($descricao)) {
        $descricao = trim($descricao);
        if ($descricao === '') {
            $descricao = null;
        }
    }

    $status = $dados['status'] ?? null;
    if ($exigirStatus) {
        if (!is_string($status) || !in_array($status, ['ativo', 'arquivado'], true)) {
            $erros[] = 'status: deve ser "ativo" ou "arquivado"';
            $status = null;
        }
    }

    return [
        'erros' => $erros,
        'dados' => ['nome' => $nome, 'descricao' => $descricao, 'status' => $status],
    ];
}
