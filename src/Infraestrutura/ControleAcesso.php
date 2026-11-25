<?php

namespace Abdm\Infraestrutura;

use PDO;

final class ControleAcesso
{
    private array $permissoesPorConta = [];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function exigirUsuarioAutenticado(): void
    {
        if (empty($_SESSION['loggedin'])) {
            Sessao::redirecionarUsuarioNaoAutenticado();
        }

        if (Sessao::sessaoExpirouPorInatividade()) {
            Sessao::limparSessaoAtual();
            Sessao::redirecionarUsuarioNaoAutenticado('timeout');
        }

        $loginId = filter_var($_SESSION['id_login'] ?? null, FILTER_VALIDATE_INT);
        if (!$loginId || $loginId < 1) {
            Sessao::limparSessaoAtual();
            Sessao::redirecionarUsuarioNaoAutenticado('invalid');
        }

        $stmt = $this->pdo->prepare('SELECT ativo, is_admin, troca_senha_obrigatoria, senha_temporaria_expira_em, senha_alterada_em, CASE WHEN senha_temporaria_expira_em IS NULL OR senha_temporaria_expira_em <= CURRENT_TIMESTAMP THEN 1 ELSE 0 END AS senha_temporaria_expirada FROM login WHERE idLogin = ?');
        $stmt->execute([$loginId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$account || (int)$account['ativo'] !== 1) {
            Sessao::limparSessaoAtual();
            Sessao::redirecionarUsuarioNaoAutenticado('inactive');
        }

        $versaoSenhaAtual = (string)($account['senha_alterada_em'] ?? '');
        $versaoSenhaSessao = $_SESSION['senha_alterada_em'] ?? null;
        if ($versaoSenhaAtual === '' || !is_string($versaoSenhaSessao) || !hash_equals($versaoSenhaAtual, $versaoSenhaSessao)) {
            Sessao::limparSessaoAtual();
            Sessao::redirecionarUsuarioNaoAutenticado('credentials_changed');
        }

        if ((int)$account['troca_senha_obrigatoria'] === 1) {
            if ((int)$account['senha_temporaria_expirada'] === 1) {
                Sessao::limparSessaoAtual();
                Sessao::redirecionarUsuarioNaoAutenticado('temporary_password_expired');
            }
        }

        $_SESSION['is_admin'] = (int)$account['is_admin'] === 1;
        $_SESSION['force_password_change'] = (int)$account['troca_senha_obrigatoria'] === 1;

        if ($_SESSION['force_password_change']) {
            $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $isPasswordPage = str_ends_with($script, '/views/minha_senha.php');
            $isPasswordAction = in_array($script, [
                BASE_URL . 'acoes/controllerUsuarios.php',
                BASE_URL . 'controllers/controllerUsuarios.php',
                BASE_URL . 'public/acoes/controllerUsuarios.php',
            ], true)
                && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
                && ($_POST['acao'] ?? '') === 'alterar_senha_propria';
            if (!$isPasswordPage && !$isPasswordAction) {
                header('Location: ' . BASE_URL . 'views/minha_senha.php');
                exit;
            }
        }

        $_SESSION['last_activity'] = time();
    }

    public function exigirUsuarioAdministrador(): void
    {
        $this->exigirUsuarioAutenticado();
        if (empty($_SESSION['is_admin'])) {
            http_response_code(403);
            exit('Administrator access required.');
        }
    }

    public static function catalogoFuncionalidadesAplicacao(): array
    {
        return [
            'imprimir_minhas_evolucoes' => ['nome' => 'Imprimir minhas evoluções', 'descricao' => 'Imprimir evoluções registradas pelo próprio profissional.'],
            'imprimir_evolucoes_profissional' => ['nome' => 'Imprimir evoluções por profissional', 'descricao' => 'Imprimir evoluções registradas por um profissional selecionado.'],
            'imprimir_evolucoes_todos' => ['nome' => 'Imprimir evoluções de todos', 'descricao' => 'Imprimir evoluções de todos os profissionais.'],
            'saude_acolhido' => ['nome' => 'Saúde dos acolhidos', 'descricao' => 'Consultar e registrar consultas, exames, alterações comportamentais e histórico de prescrições.'],
            'acolhidos' => ['nome' => 'Acolhidos', 'descricao' => 'Consultar e gerenciar cadastros de acolhidos.'],
            'prescricoes' => ['nome' => 'Prescrições e horários', 'descricao' => 'Criar prescrições e configurar períodos e horários.'],
            'dispensacao' => ['nome' => 'Dispensação', 'descricao' => 'Registrar e consultar a dispensação de medicamentos.'],
            'estoque' => ['nome' => 'Farmácia e estoque', 'descricao' => 'Cadastrar medicamentos e gerenciar entradas e saídas do estoque.'],
            'calendario' => ['nome' => 'Calendário', 'descricao' => 'Consultar a agenda incorporada do Google Agenda.'],
            'gerenciar_calendario' => ['nome' => 'Gerenciar calendário', 'descricao' => 'Alterar o endereço exibido na página Calendário.'],
            'relatorio_diario' => ['nome' => 'Relatório diário', 'descricao' => 'Consultar e registrar relatos no livro diário da unidade.'],
            'pia' => ['nome' => 'Plano Individual de Atendimento (PIA)', 'descricao' => 'Consultar, preencher, atualizar e imprimir o PIA dos acolhidos.'],
            'gerenciar_pia_campos' => ['nome' => 'Gerenciar campos do PIA', 'descricao' => 'Adicionar, editar, ativar e remover campos do formulário PIA.'],
            'registrar_evolucao_acolhido' => ['nome' => 'Registrar evolução de acolhido', 'descricao' => 'Criar e registrar novas evoluções para os acolhidos.'],
            'consultar_minhas_evolucoes' => ['nome' => 'Consultar minhas evoluções', 'descricao' => 'Consultar somente as evoluções registradas pelo próprio usuário.'],
            'consultar_todas_evolucoes' => ['nome' => 'Consultar todas as evoluções', 'descricao' => 'Consultar evoluções registradas por todos os profissionais.'],
            'imprimir_evolucao_acolhido' => ['nome' => 'Imprimir evolução de acolhido', 'descricao' => 'Imprimir evoluções de acolhidos.'],
            'informacoes' => ['nome' => 'Informações', 'descricao' => 'Visualizar os links de informações compartilhados no menu.'],
            'gerenciar_informacoes' => ['nome' => 'Gerenciar links de informações', 'descricao' => 'Criar, editar e remover os links exibidos no menu Informações.'],
        ];
    }

    public function usuarioPode(string $funcionalidade): bool
    {
        if (!array_key_exists($funcionalidade, self::catalogoFuncionalidadesAplicacao())) {
            return false;
        }
        if (!empty($_SESSION['is_admin'])) {
            return true;
        }

        $loginId = (int)($_SESSION['id_login'] ?? 0);
        if (!isset($this->permissoesPorConta[$loginId][$funcionalidade])) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM login_funcionalidades WHERE Login_idLogin = ? AND funcionalidade = ? LIMIT 1');
            $stmt->execute([$loginId, $funcionalidade]);
            $this->permissoesPorConta[$loginId][$funcionalidade] = (bool)$stmt->fetchColumn();
        }
        return $this->permissoesPorConta[$loginId][$funcionalidade];
    }

    public function exigirPermissao(string $funcionalidade): void
    {
        $this->exigirUsuarioAutenticado();
        if (!$this->usuarioPode($funcionalidade)) {
            http_response_code(403);
            exit('Você não tem permissão para acessar esta funcionalidade.');
        }
    }
}
