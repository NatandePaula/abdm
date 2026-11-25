<?php

namespace Abdm\Infraestrutura;

final class Sessao
{
    public static function tokenCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function campoTokenCsrf(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::tokenCsrf(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function exigirTokenCsrfValido(): void
    {
        $submitted = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($submitted) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
            http_response_code(403);
            exit('Token da solicitação inválido. Atualize a página e tente novamente.');
        }
    }

    public static function requisicaoAutomatizada(): bool
    {
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        if (str_contains($accept, 'application/json')) {
            return true;
        }

        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        foreach (['/dao/get_medicamentos_json.php', '/dao/controleTempo.php'] as $endpoint) {
            if (str_ends_with($script, $endpoint)) {
                return true;
            }
        }
        return false;
    }

    public static function limparSessaoAtual(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function redirecionarUsuarioNaoAutenticado(?string $reason = null): void
    {
        if (!self::requisicaoAutomatizada()) {
            $loginUrl = BASE_URL . 'index.php';
            if ($reason === 'timeout') {
                $loginUrl .= '?timeout=1';
            } elseif ($reason === 'invalid') {
                $loginUrl .= '?acesso=invalido';
            } elseif ($reason === 'inactive') {
                $loginUrl .= '?acesso=desativado';
            } elseif ($reason === 'temporary_password_expired') {
                $loginUrl .= '?senha_temporaria_expirada=1';
            } elseif ($reason === 'credentials_changed') {
                $loginUrl .= '?acesso=credencial_alterada';
            }
            header('Location: ' . $loginUrl);
            exit;
        }

        http_response_code(401);
        if (str_contains(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
            || str_ends_with(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/dao/get_medicamentos_json.php')
            ) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Autenticação necessária.']);
        } else {
            exit('Authentication required.');
        }
        exit;
    }

    public static function sessaoExpirouPorInatividade(): bool
    {
        return isset($_SESSION['last_activity'])
            && time() - (int)$_SESSION['last_activity'] > TEMPO_LIMITE_INATIVIDADE;
    }
}
