<?php

require_once __DIR__ . '/response.php';

function verificar_autenticacao(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
      
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        
        json_response(false, 'Acesso não autorizado.', null, 401);
    }
}