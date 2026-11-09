<?php

require_once __DIR__ . '/response.php';

function verificar_autenticacao(): void
{
    // Sem sessão ativa, o PHP não sabe quem você é: acorda ele primeiro.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Sem crachá (usuario_id) na sessão, a porta fecha com 401.
    if (empty($_SESSION['usuario_id'])) {
        json_response(false, 'Não autenticado. Faça login para continuar.', null, 401);
    }
}
