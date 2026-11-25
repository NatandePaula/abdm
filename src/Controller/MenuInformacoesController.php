<?php

namespace Abdm\Controller;

use PDO;

final class MenuInformacoesController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function cabecalho(\Abdm\Infraestrutura\ControleAcesso $controleAcesso): array
    {
        $mostrarMenuInformacoes = $controleAcesso->usuarioPode('informacoes')
            || $controleAcesso->usuarioPode('gerenciar_informacoes');
        $linksMenuInformacoes = $mostrarMenuInformacoes
            ? (new \Abdm\Model\Informacoes($this->pdo))->linksDoMenu()
            : [];
        $nomeUsuarioNav = trim((string)($_SESSION['username'] ?? ''));
        $idLoginNav = (int)($_SESSION['id_login'] ?? 0);
        if ($idLoginNav > 0) {
            $nomeCompletoNav = trim((string)(new \Abdm\Model\Usuarios($this->pdo))->nomeDoPerfil($idLoginNav));
            $partesNomeNav = preg_split('/\s+/u', $nomeCompletoNav, 2, PREG_SPLIT_NO_EMPTY) ?: [];
            $nomeUsuarioNav = $partesNomeNav[0] ?? $nomeUsuarioNav;
        }

        return [
            'mostrarMenuInformacoes' => $mostrarMenuInformacoes,
            'linksMenuInformacoes' => $linksMenuInformacoes,
            'nomeUsuarioNav' => $nomeUsuarioNav === '' ? 'Usuário' : $nomeUsuarioNav,
        ];
    }

    public function gerenciar(): array
    {
        $pdo = $this->pdo;
        $linksInformacoes = (new \Abdm\Model\Informacoes($pdo))->links();
        $editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
        $linkEditar = null;
        if ($editarId) {
            foreach ($linksInformacoes as $link) {
                if ((int)$link['idInformacaoLink'] === $editarId) {
                    $linkEditar = $link;
                    break;
                }
            }
            if ($linkEditar === null) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'O link solicitado n' . "\u{00E3}" . 'o foi encontrado.'];
                header('Location: ' . BASE_URL . 'views/gerenciar_informacoes.php');
                exit;
            }
        }

        $formRetentativa = $_SESSION['informacao_link_retentativa'] ?? null;
        unset($_SESSION['informacao_link_retentativa']);
        $idFormulario = $linkEditar ? (int)$linkEditar['idInformacaoLink'] : 0;
        $formRetentativaValida = is_array($formRetentativa) && (int)($formRetentativa['id'] ?? -1) === $idFormulario;
        if ($formRetentativaValida) {
            $linkEditar = [
                'idInformacaoLink' => $idFormulario,
                'titulo' => is_string($formRetentativa['titulo'] ?? null) ? $formRetentativa['titulo'] : '',
                'url' => is_string($formRetentativa['url'] ?? null) ? $formRetentativa['url'] : '',
            ];
        }
        $estaEditando = $linkEditar !== null && (int)($linkEditar['idInformacaoLink'] ?? 0) > 0;

        return [
            'linksInformacoes' => $linksInformacoes ?? null,
            'linkEditar' => $linkEditar ?? null,
            'link' => $link ?? null,
            'estaEditando' => $estaEditando ?? null,
        ];
    }
}
