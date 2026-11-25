<?php

require_once dirname(__DIR__) . '/src/autoload.php';
ini_set('display_errors', '0');
ini_set('zend.exception_ignore_args', '1');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/caminhos.php';
date_default_timezone_set('America/Sao_Paulo');

// Limite de inatividade e antecedência do aviso, em segundos.
const TEMPO_LIMITE_INATIVIDADE = 9000;
const TEMPO_AVISO_PREVIO = 300;

$config ??= is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];
$host = $config['db_host'] ?? '127.0.0.1';
$db = $config['db_name'] ?? 'abdm';
$porta = filter_var(
    $config['db_port'] ?? 3306,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 65535]]
);
$user = $config['db_user'] ?? null;
$pass = $config['db_password'] ?? null;
$encryptionKey = $config['encryption_key'] ?? null;
if (!is_array($config) || !is_string($host) || !is_string($db)
    || !is_string($user) || trim($user) === '' || !is_string($pass) || $porta === false
    || !is_string($encryptionKey) || strlen($encryptionKey) !== 32) {
    error_log('A configuração do banco de dados ou da criptografia do ABDM está ausente ou inválida.');
    http_response_code(500);
    die('A configuração do servidor está incompleta.');
}

define('ENCRYPTION_KEY', $encryptionKey);
define('ENCRYPTION_METHOD', 'aes-256-cbc');
$gotasPorMl = $config['gotas_por_ml'] ?? [];
define('GOTAS_POR_ML', is_array($gotasPorMl) ? $gotasPorMl : []);

$host = trim($host) ?: '127.0.0.1';
$db = trim($db) ?: 'abdm';

try {
    $dsn = "mysql:host=$host;port=$porta;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log('Falha na conexão com o banco de dados. Código: ' . $e->getCode());
    http_response_code(500);
    die('Não foi possível conectar ao banco de dados.');
}
