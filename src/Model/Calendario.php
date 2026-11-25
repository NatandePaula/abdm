<?php

namespace Abdm\Model;

use PDO;
use PDOException;

final class Calendario
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function urlCalendarioIncorporadaPermitida(string $url): bool
    {
        if ($url === '' || strlen($url) > 8192 || preg_match('/[\p{C}]/u', $url)) {
            return false;
        }

        $parts = parse_url($url);
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && is_array($parts)
            && strtolower((string)($parts['scheme'] ?? '')) === 'https'
            && strtolower((string)($parts['host'] ?? '')) === 'calendar.google.com'
            && rtrim((string)($parts['path'] ?? ''), '/') === '/calendar/embed'
            && (!isset($parts['port']) || $parts['port'] === 443)
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }

    public function lerConfiguracaoCalendario(): array
    {
        $pdo = $this->pdo;
        try {
            $url = $pdo->query('SELECT url FROM calendario_configuracao WHERE id = 1')->fetchColumn();
        } catch (PDOException $e) {
            return [
                'available' => false,
                'url' => '',
            ];
        }

        if (is_string($url) && self::urlCalendarioIncorporadaPermitida($url)) {
            return ['available' => true, 'url' => $url];
        }

        return ['available' => true, 'url' => ''];
    }
}
