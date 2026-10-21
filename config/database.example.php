
<?php

define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'taskflow');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');

try {
    $dsn = "pgsql:host=" . DB_HOST .
           ";port=" . DB_PORT .
           ";dbname=" . DB_NAME;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    exit('Erro interno de conexão com o banco de dados.');
}
