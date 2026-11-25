<?php

namespace Abdm\Model;

use Abdm\Infraestrutura\Criptografia;

use DateTimeImmutable;
use PDO;
use PDOStatement;
use Throwable;

final class Pia
{
    public function __construct(private readonly ?PDO $pdo = null)
    {
    }

    public static function piaSecoesPadrao(): array
    {
        return [
            'identificacao_servico' => ['titulo' => '1. IDENTIFICAÇÃO DO SERVIÇO DE ACOLHIMENTO', 'ordem' => 10],
            'acolhido' => ['titulo' => '2. INFORMAÇÕES SOBRE O ACOLHIDO', 'ordem' => 20],
            'documentos' => ['titulo' => '3. SITUAÇÃO DOCUMENTAL', 'ordem' => 30],
            'beneficios' => ['titulo' => '3. RENDA/BENEFÍCIOS', 'ordem' => 31],
            'saude' => ['titulo' => '4. SAÚDE', 'ordem' => 40],
            'composicao_familiar' => ['titulo' => '5. COMPOSIÇÃO FAMILIAR', 'ordem' => 50],
            'situacao_familiar' => ['titulo' => '6. SITUAÇÃO FAMILIAR', 'ordem' => 60],
            'acolhimento' => ['titulo' => '7. INFORMAÇÕES ACERCA DO ACOLHIMENTO', 'ordem' => 70],
            'rede' => ['titulo' => '8. REDE SOCIOASSISTENCIAL', 'ordem' => 80],
            'procedimentos' => ['titulo' => '9. PROCEDIMENTOS REALIZADOS PELA EQUIPE TÉCNICA E COORDENAÇÃO DO SERVIÇO', 'ordem' => 90],
            'plano' => ['titulo' => '10. PLANO DE ATIVIDADES', 'ordem' => 100],
        ];
    }

    public static function piaConfiguracaoServicoPadrao(): array
    {
        return [
            'nome' => 'Associação Beneficente Dias Melhores – ABDM',
            'endereco' => 'Rua Mariana de Souza Barros, 01. Celina – Alegre/ES',
            'telefone' => '28 99937-8167',
        ];
    }

    public function piaConfiguracaoServico(): array
    {
        $padrao = self::piaConfiguracaoServicoPadrao();
        $pdo = $this->pdo;
        if (!$pdo instanceof PDO) {
            return $padrao;
        }

        try {
            $configuracao = $pdo->query('SELECT nome, endereco, telefone FROM pia_servico_configuracao WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $exception) {
            return $padrao;
        }

        if (!is_array($configuracao)) {
            return $padrao;
        }

        foreach ($padrao as $chave => $valorPadrao) {
            if (!isset($configuracao[$chave]) || trim((string)$configuracao[$chave]) === '') {
                $configuracao[$chave] = $chave === 'telefone' ? '' : $valorPadrao;
            }
        }

        return $configuracao;
    }

    public function piaServicoConfiguracaoDisponivel(): bool
    {
        $pdo = $this->pdo;
        if (!$pdo instanceof PDO) {
            return false;
        }

        try {
            return $pdo->query('SELECT id FROM pia_servico_configuracao LIMIT 1') instanceof PDOStatement;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function piaVisibilidadeCampos(): array
    {
        if ($this->pdo === null) {
            return [];
        }
        $campos = $this->pdo->query('SELECT chave, ativo FROM pia_campos')->fetchAll(PDO::FETCH_ASSOC);
        return array_column($campos, 'ativo', 'chave');
    }

    public function piaSecoesConfiguradas(): array
    {
        $padrao = self::piaSecoesPadrao();
        try {
            $pdo = $this->pdo;
            if (!$pdo instanceof PDO) return array_map(static fn(string $chave, array $item): array => ['chave' => $chave, 'titulo' => $item['titulo'], 'ordem' => $item['ordem'], 'fixa' => 1, 'ativo' => 1], array_keys($padrao), array_values($padrao));
            $linhas = $pdo->query('SELECT chave, titulo, ordem, fixa, ativo FROM pia_secoes ORDER BY ordem, chave')->fetchAll(PDO::FETCH_ASSOC);
            if (!$linhas) return array_map(static fn(string $chave, array $item): array => ['chave' => $chave, 'titulo' => $item['titulo'], 'ordem' => $item['ordem'], 'fixa' => 1, 'ativo' => 1], array_keys($padrao), array_values($padrao));
            return $linhas;
        } catch (Throwable $exception) {
            return array_map(static fn(string $chave, array $item): array => ['chave' => $chave, 'titulo' => $item['titulo'], 'ordem' => $item['ordem'], 'fixa' => 1, 'ativo' => 1], array_keys($padrao), array_values($padrao));
        }
    }

    public function piaSecoes(bool $incluirInativas = false): array
    {
        $resultado = [];
        foreach ($this->piaSecoesConfiguradas() as $secao) {
            if (!$incluirInativas && (int)($secao['ativo'] ?? 1) !== 1) continue;
            $resultado[(string)$secao['chave']] = (string)$secao['titulo'];
        }
        unset($resultado['identificacao_servico']);
        return $resultado;
    }

    public function piaSecaoAtiva(string $chave): bool
    {
        foreach ($this->piaSecoesConfiguradas() as $secao) {
            if ((string)$secao['chave'] === $chave) return (int)($secao['ativo'] ?? 1) === 1;
        }
        return false;
    }

    public static function piaOpcoesCampo(array $campo): array
    {
        $opcoes = (string)($campo['opcoes'] ?? '');
        return $opcoes === '' ? [] : array_values(array_filter(array_map('trim', explode('|', $opcoes)), static fn(string $opcao): bool => $opcao !== ''));
    }

    public static function piaDadosJsonParaLista($json): array
    {
        if (!is_string($json) || $json === '') {
            return [];
        }
        $dados = json_decode($json, true);
        return is_array($dados) ? $dados : [];
    }

    public static function piaValorInicialCadastroCampo(string $chave, array $acolhido, array $dadosCadastro, array $familiares = [], array $cids = [])
    {
        if ($chave === 'cid10') {
            return implode(', ', array_values(array_filter(array_map(static fn(array $cid): string => strtoupper(trim((string)($cid['codigo'] ?? ''))), $cids))));
        }

        if (array_key_exists($chave, $dadosCadastro)) {
            $valorSalvo = $dadosCadastro[$chave];
            if ($valorSalvo !== '' && $valorSalvo !== null && $valorSalvo !== []) {
                return $valorSalvo;
            }
        }

        if ($chave === 'motivo_acolhimento') {
            return (string)($acolhido['cadastro_motivo'] ?? $acolhido['motivoInstitucionalizacao'] ?? '');
        }
        if ($chave === 'composicao_familiar') {
            return array_values(array_map(static fn(array $familiar): array => [
                'nome' => (string)($familiar['nome'] ?? ''),
                'parentesco' => (string)($familiar['grauParentesco'] ?? ''),
                'ocupacao' => '',
                'data_nascimento' => (string)($familiar['dataNascimento'] ?? ''),
                'sexo' => '',
            ], $familiares));
        }
        if ($chave === 'origem_encaminhamento') {
            $origem = function_exists('mb_strtolower')
                ? mb_strtolower((string)($acolhido['cadastro_origem'] ?? $acolhido['origemAcolhimento'] ?? ''), 'UTF-8')
                : strtolower((string)($acolhido['cadastro_origem'] ?? $acolhido['origemAcolhimento'] ?? ''));
            $marcados = [];
            if (str_contains($origem, 'creas')) $marcados[] = 'CREAS';
            if (str_contains($origem, 'jud') || str_contains($origem, 'vara')) $marcados[] = 'Judiciário';
            if (str_contains($origem, 'promot') || str_contains($origem, 'minist')) $marcados[] = 'Ministério Público';
            return $marcados;
        }
        if ($chave === 'beneficios') {
            $beneficio = function_exists('mb_strtolower')
                ? mb_strtolower((string)($acolhido['cadastro_beneficio'] ?? $acolhido['tipoBeneficio'] ?? ''), 'UTF-8')
                : strtolower((string)($acolhido['cadastro_beneficio'] ?? $acolhido['tipoBeneficio'] ?? ''));
            if ($beneficio === '') return [];
            $marcados = [];
            if (str_contains($beneficio, 'nenhum') || str_contains($beneficio, 'não recebe') || str_contains($beneficio, 'nao recebe')) $marcados[] = 'nenhum';
            if (str_contains($beneficio, 'aposent')) $marcados[] = 'aposentadoria';
            if (str_contains($beneficio, 'pensão') || str_contains($beneficio, 'pensao')) $marcados[] = 'pensao';
            if (str_contains($beneficio, 'bpc') || str_contains($beneficio, 'loas')) $marcados[] = 'bpc';
            if (str_contains($beneficio, 'atividade') || str_contains($beneficio, 'remuner')) $marcados[] = 'atividade';
            if (!$marcados) $marcados[] = 'outro';
            return array_values(array_unique($marcados));
        }
        if ($chave === 'documentos') {
            $documentos = [];
            $cpf = (string)($acolhido['acolhido_cpf'] ?? $acolhido['cpf'] ?? '');
            if ($cpf !== '' && defined('ENCRYPTION_KEY')) $cpf = (string)Criptografia::descriptografar($cpf);
            if ($cpf !== '') $documentos['cpf'] = ['situacao' => 'possui', 'numero' => $cpf];
            $numeroSus = DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '');
            if ($numeroSus !== '') $documentos['sus'] = ['situacao' => 'possui', 'numero' => $numeroSus];
            $tipoDocumento = (string)($acolhido['tipo_documento'] ?? '');
            $numeroDocumento = DadosAcolhido::acolhidoDocumentoDescriptografar($acolhido['documento_identificacao'] ?? '');
            if ($tipoDocumento !== '' || $numeroDocumento !== '') {
                $tipoNormalizado = function_exists('mb_strtolower') ? mb_strtolower($tipoDocumento, 'UTF-8') : strtolower($tipoDocumento);
                $tipoNormalizado = strtr($tipoNormalizado, ['á'=>'a','ã'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ç'=>'c']);
                $chaveDocumento = str_contains($tipoNormalizado, 'certid') ? 'certidao'
                    : (str_contains($tipoNormalizado, 'titulo') ? 'titulo_eleitoral'
                    : (str_contains($tipoNormalizado, 'ctps') || str_contains($tipoNormalizado, 'trabalho') ? 'ctps'
                    : (str_contains($tipoNormalizado, 'sus') ? 'sus'
                    : (str_contains($tipoNormalizado, 'rg') || str_contains($tipoNormalizado, 'identidade') ? 'rg' : 'outros'))));
                $documentos[$chaveDocumento] = ['situacao' => 'possui', 'numero' => trim($tipoDocumento . ' ' . $numeroDocumento)];
            }
            return $documentos;
        }

        return $dadosCadastro[$chave] ?? '';
    }

    public static function piaDataValida(string $valor): bool
    {
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        $erros = DateTimeImmutable::getLastErrors();
        return $data !== false && $data->format('Y-m-d') === $valor
            && (!is_array($erros) || ($erros['warning_count'] === 0 && $erros['error_count'] === 0));
    }

    public static function piaEscaparHtml($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function piaDataExibicao($valor): string
    {
        if (!is_string($valor) || $valor === '' || !self::piaDataValida($valor)) {
            return '';
        }
        return (new DateTimeImmutable($valor))->format('d/m/Y');
    }

    public static function piaTextoTamanho(string $texto): int
    {
        return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
    }

    public static function piaTextoRecortar(string $texto, int $tamanho): string
    {
        return function_exists('mb_substr') ? mb_substr($texto, 0, $tamanho, 'UTF-8') : substr($texto, 0, $tamanho);
    }
}
