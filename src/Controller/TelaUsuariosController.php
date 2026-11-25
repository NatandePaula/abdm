<?php

namespace Abdm\Controller;

use PDO;

final class TelaUsuariosController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function preparar(): array
    {
        $pdo = $this->pdo;
        $modoInativos = ($_GET['inativos'] ?? '') === '1';
        $novoUsuario = ($_GET['novo'] ?? '') === '1';
        $urlListaUsuarios = BASE_URL . 'views/usuarios.php' . ($modoInativos ? '?inativos=1' : '');

        $usuarios = (new \Abdm\Model\Usuarios($pdo))->contas();
        foreach ($usuarios as &$usuarioCpf):
            $cpfArmazenado = $usuarioCpf['perfil_cpf'] ?? null;
            $usuarioCpf['perfil_cpf'] = is_string($cpfArmazenado) && $cpfArmazenado !== ''
                ? \Abdm\Infraestrutura\Criptografia::descriptografar($cpfArmazenado)
                : '';
        endforeach;
        unset($usuarioCpf);
        $duplicadosPerfil = (new \Abdm\Model\Usuarios($pdo))->perfisDuplicados();
        $duplicadosPerfil = array_fill_keys(array_map('intval', $duplicadosPerfil), true);
        $permissoesPorUsuario = [];
        foreach ((new \Abdm\Model\Usuarios($pdo))->permissoes() as $permissao) {
            $permissoesPorUsuario[(int)$permissao['Login_idLogin']][] = $permissao['funcionalidade'];
        }
        $permissoesCatalogo = \Abdm\Infraestrutura\ControleAcesso::catalogoFuncionalidadesAplicacao();
        $usuarioEditarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
        $usuarioEditar = null;
        if ($usuarioEditarId) {
            foreach ($usuarios as $contaUsuario) {
                if ((int)$contaUsuario['idLogin'] === $usuarioEditarId) {
                    $usuarioEditar = $contaUsuario;
                    break;
                }
            }
            if ($usuarioEditar === null) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Usuário não encontrado.'];
                header('Location: ' . $urlListaUsuarios);
                exit;
            }
        }
        $formularioRetentativa = $_SESSION['usuarios_form_retentativa'] ?? [];
        unset($_SESSION['usuarios_form_retentativa']);
        $retentativaCadastro = is_array($formularioRetentativa)
            && ($formularioRetentativa['acao'] ?? '') === 'criar_usuario'
            && $novoUsuario
            && !$usuarioEditar;
        $retentativaEdicao = is_array($formularioRetentativa)
            && ($formularioRetentativa['acao'] ?? '') === 'atualizar_usuario'
            && $usuarioEditar
            && (int)($formularioRetentativa['id_login'] ?? 0) === (int)$usuarioEditar['idLogin'];
        if ($retentativaEdicao && isset($formularioRetentativa['login'])) {
            $usuarioEditar['login'] = $formularioRetentativa['login'];
        }

        return [
            'modoInativos' => $modoInativos ?? null,
            'novoUsuario' => $novoUsuario ?? null,
            'urlListaUsuarios' => $urlListaUsuarios ?? null,
            'usuarios' => $usuarios ?? null,
            'duplicadosPerfil' => $duplicadosPerfil ?? null,
            'permissoesPorUsuario' => $permissoesPorUsuario ?? null,
            'usuarioEditar' => $usuarioEditar ?? null,
            'formularioRetentativa' => $formularioRetentativa ?? null,
            'retentativaCadastro' => $retentativaCadastro ?? null,
            'retentativaEdicao' => $retentativaEdicao ?? null,
        ];
    }
}
