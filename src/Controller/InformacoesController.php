<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;
use Abdm\Infraestrutura\Sessao;

use PDO;
use Throwable;
use InvalidArgumentException;

final class InformacoesController
{
    private readonly ControleAcesso $controleAcesso;

    public function __construct(private readonly PDO $pdo)
    {
        $this->controleAcesso = new ControleAcesso($pdo);
    }

    public function executar(): void
    {
        $pdo = $this->pdo;
        $this->controleAcesso->exigirPermissao('gerenciar_informacoes');
        Sessao::exigirTokenCsrfValido();

        $acao = $_POST['acao'] ?? '';
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $titulo = $_POST['titulo'] ?? '';
        $url = $_POST['url'] ?? '';
        $destinoErro = 'views/gerenciar_informacoes.php';

        if ($acao === 'salvar' && $id !== false && $id !== null && $id > 0) {
            $destinoErro .= '?editar=' . $id;
        }

        try {
            if (!is_string($acao)) {
                throw new InvalidArgumentException("A\u{00E7}\u{00E3}o inv\u{00E1}lida.");
            }

            if ($acao === 'salvar') {
                if (!is_string($titulo) || !is_string($url)) {
                    throw new InvalidArgumentException('Informe o t' . "\u{00ED}" . 'tulo e o endere' . "\u{00E7}o" . ' do link.');
                }

                $titulo = trim($titulo);
                $url = trim($url);
                $comprimentoTitulo = function_exists('mb_strlen') ? mb_strlen($titulo, 'UTF-8') : strlen($titulo);
                if ($comprimentoTitulo < 1 || $comprimentoTitulo > 100 || preg_match('/[\p{C}]/u', $titulo)) {
                    throw new InvalidArgumentException('O t' . "\u{00ED}" . 'tulo deve ter de 1 a 100 caracteres.');
                }

                $partesUrl = parse_url($url);
                if (strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false
                    || !is_array($partesUrl)
                    || !in_array(strtolower((string)($partesUrl['scheme'] ?? '')), ['http', 'https'], true)
                    || empty($partesUrl['host']) || isset($partesUrl['user']) || isset($partesUrl['pass'])) {
                    throw new InvalidArgumentException('Informe um endere' . "\u{00E7}o" . ' HTTP ou HTTPS v' . "\u{00E1}" . 'lido.');
                }

                if ($id !== false && $id !== null && $id > 0) {
                    $stmt = (new \Abdm\Model\PersistenciaInformacoes($pdo))->prepararConsultaLinkInformativoComIdInformacaoLinkPorIdInformacaoLink();
                    $stmt->execute([$id]);
                    if (!$stmt->fetchColumn()) {
                        throw new InvalidArgumentException('O link que voc' . "\u{00EA}" . ' est' . "\u{00E1}" . ' editando n' . "\u{00E3}" . 'o existe mais.');
                    }
                    $stmt = (new \Abdm\Model\PersistenciaInformacoes($pdo))->prepararAtualizacaoLinkInformativoDeTitulo();
                    $stmt->execute([$titulo, $url, $id]);
                    $mensagem = 'Link atualizado.';
                } else {
                    $ordem = (int)(new \Abdm\Model\PersistenciaInformacoes($pdo))->consultarLinkInformativo()->fetchColumn();
                    $stmt = (new \Abdm\Model\PersistenciaInformacoes($pdo))->prepararInsercaoLinkInformativo();
                    $stmt->execute([$titulo, $url, $ordem]);
                    $mensagem = 'Link adicionado ao menu Informa' . "\u{00E7}\u{00F5}" . 'es.';
                }
                unset($_SESSION['informacao_link_retentativa']);
            } elseif ($acao === 'excluir') {
                if ($id === false || $id === null || $id < 1) {
                    throw new InvalidArgumentException('Link inv' . "\u{00E1}" . 'lido.');
                }
                $stmt = (new \Abdm\Model\PersistenciaInformacoes($pdo))->prepararExclusaoLinkInformativo();
                $stmt->execute([$id]);
                if ($stmt->rowCount() === 0) {
                    throw new InvalidArgumentException('O link j' . "\u{00E1}" . ' foi removido.');
                }
                $mensagem = 'Link removido do menu Informa' . "\u{00E7}\u{00F5}" . 'es.';
            } else {
                throw new InvalidArgumentException('A' . "\u{00E7}\u{00E3}" . 'o desconhecida.');
            }

            $_SESSION['flash_message'] = ['type' => 'success', 'message' => $mensagem];
            header('Location: ' . BASE_URL . 'views/gerenciar_informacoes.php');
            exit;
        } catch (InvalidArgumentException $e) {
            if ($acao === 'salvar') {
                $_SESSION['informacao_link_retentativa'] = [
                    'id' => ($id !== false && $id !== null && $id > 0) ? (int)$id : 0,
                    'titulo' => is_string($titulo) ? substr($titulo, 0, 300) : '',
                    'url' => is_string($url) ? substr($url, 0, 2048) : '',
                ];
            }
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('Falha na operação de links informativos. Código: ' . $e->getCode());
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'N' . "\u{00E3}" . 'o foi poss' . "\u{00ED}" . 'vel salvar as informa' . "\u{00E7}\u{00F5}" . 'es.'];
        }

        header('Location: ' . BASE_URL . $destinoErro);
        exit;
    }
}
