<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\Sessao;

use PDO;
use PDOException;

final class LoginController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function executar(): string
    {
        $pdo = $this->pdo;
        $mensagemErro = "";
        if (($_GET['senha_temporaria_expirada'] ?? '') === '1') {
            $mensagemErro = 'A senha temporária expirou. Solicite ao administrador uma nova redefinição.';
        } elseif (($_GET['acesso'] ?? '') === 'credencial_alterada') {
            $mensagemErro = 'Sua senha foi alterada. Entre novamente com a senha atualizada.';
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            Sessao::exigirTokenCsrfValido();

            $loginInformado = $_POST['username'] ?? '';
            $senhaInformada = $_POST['password'] ?? '';
            if (!is_string($loginInformado) || !is_string($senhaInformada)) {
                $loginInformado = '';
                $senhaInformada = '';
            } else {
                $loginInformado = trim($loginInformado);
            }

            if (empty($loginInformado) || empty($senhaInformada)) {
                $mensagemErro = "Por favor, preencha todos os campos.";
            } else {

                try {

                    $stmt = (new \Abdm\Model\PersistenciaAutenticacao($pdo))->prepararConsultaContaComIdLoginPorLogin();
                    $stmt->bindParam(':login', $loginInformado, PDO::PARAM_STR);
                    $stmt->execute();

                    $conta = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($conta) {
                        $senhaTemporariaExpirada = (int)$conta['troca_senha_obrigatoria'] === 1
                            && (int)$conta['senha_temporaria_expirada'] === 1;

                        if ($senhaTemporariaExpirada) {
                            $mensagemErro = 'A senha temporária expirou. Solicite ao administrador uma nova redefinição.';
                        } else {
                            $senhaArmazenada = $conta['senha'];
                            $ehHash = password_get_info($senhaArmazenada)['algo'] !== null;
                            $senhaValida = $ehHash
                                ? password_verify($senhaInformada, $senhaArmazenada)
                                : hash_equals($senhaArmazenada, $senhaInformada);

                            if ($senhaValida) {
                                if (!$ehHash) {
                                    $stmtAtualizarSenha = (new \Abdm\Model\PersistenciaAutenticacao($pdo))->prepararAtualizacaoContaDeSenha();
                                    $stmtAtualizarSenha->execute([
                                        password_hash($senhaInformada, PASSWORD_DEFAULT),
                                        $conta['idLogin'],
                                        $senhaArmazenada,
                                        $conta['senha_alterada_em'],
                                    ]);
                                    if ($stmtAtualizarSenha->rowCount() !== 1) {
                                        header('Location: ' . BASE_URL . 'index.php?acesso=credencial_alterada');
                                        exit;
                                    }
                                }

                                session_regenerate_id(true);
                                $_SESSION['loggedin'] = true;
                                $_SESSION['id_login'] = $conta['idLogin'];
                                $_SESSION['username'] = $loginInformado;
                                $_SESSION['is_admin'] = (int)$conta['is_admin'] === 1;
                                $_SESSION['force_password_change'] = (int)$conta['troca_senha_obrigatoria'] === 1;
                                $_SESSION['senha_alterada_em'] = (string)$conta['senha_alterada_em'];
                                $_SESSION['last_activity'] = time();
                                unset($_SESSION['csrf_token']);

                                header('Location: ' . ($_SESSION['force_password_change'] ? 'views/minha_senha.php' : 'views/home.php'));
                                exit;
                            } else {
                                $mensagemErro = "Login ou senha incorretos.";
                            }
                        }
                    } else {
                        $mensagemErro = "Login ou senha incorretos.";
                    }

                } catch (PDOException $e) {
                    error_log('Falha no banco durante a autenticação. Código: ' . $e->getCode());
                    $mensagemErro = "Não foi possível efetuar o login.";
                }
            }
        }

        return $mensagemErro;
    }
}
