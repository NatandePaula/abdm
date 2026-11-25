<?php

namespace Abdm\Model;

use Abdm\Infraestrutura\Criptografia;

use DateTimeImmutable;
use RuntimeException;
use InvalidArgumentException;

final class DadosAcolhido
{
    public static function acolhidoOpcoesSexo(): array
    {
        return [
            'feminino' => 'Feminino',
            'masculino' => 'Masculino',
            'nao_informado' => 'Não informado',
        ];
    }

    public static function acolhidoSexoParaBanco($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (!is_string($valor)) {
            throw new InvalidArgumentException('Selecione uma opção válida para o sexo do acolhido.');
        }

        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }
        if (!array_key_exists($valor, self::acolhidoOpcoesSexo())) {
            throw new InvalidArgumentException('Selecione uma opção válida para o sexo do acolhido.');
        }

        return $valor;
    }

    public static function acolhidoSexoRotulo($valor): string
    {
        if (!is_string($valor)) {
            return 'Não informado';
        }

        return self::acolhidoOpcoesSexo()[$valor] ?? 'Não informado';
    }

    public static function acolhidoIdade($dataNascimento): ?int
    {
        if (!is_string($dataNascimento) || trim($dataNascimento) === '') {
            return null;
        }

        $nascimento = DateTimeImmutable::createFromFormat('!Y-m-d', trim($dataNascimento));
        $erros = DateTimeImmutable::getLastErrors();
        if ($nascimento === false || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
            return null;
        }

        $hoje = new DateTimeImmutable('today');
        if ($nascimento > $hoje) {
            return null;
        }

        return $nascimento->diff($hoje)->y;
    }

    public static function acolhidoCepParaBanco($valor, string $campo = 'CEP', bool $obrigatorio = true): string
    {
        $texto = is_scalar($valor) ? (string)$valor : '';
        $cep = preg_replace('/\D+/', '', $texto) ?? '';
        if ($cep === '' && !$obrigatorio) {
            return '';
        }
        if (strlen($cep) !== 8) {
            throw new InvalidArgumentException('Informe ' . $campo . ' com 8 dígitos.');
        }
        return $cep;
    }

    public static function acolhidoDadosComplementaresJsonParaLista(?string $json): array
    {
        if (!is_string($json) || trim($json) === '') {
            return [];
        }
        $dados = json_decode($json, true);
        return is_array($dados) ? $dados : [];
    }

    public static function acolhidoDocumentoPareceCriptografado(string $valor): bool
    {
        $decodificado = base64_decode($valor, true);
        if ($decodificado === false || !str_contains($decodificado, '::')) {
            return false;
        }

        [$conteudo, $iv] = explode('::', $decodificado, 2);
        if (!defined('ENCRYPTION_METHOD') || !function_exists('openssl_cipher_iv_length')) {
            return false;
        }
        $tamanhoIv = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        $conteudoDecodificado = base64_decode($conteudo, true);
        return $tamanhoIv > 0 && strlen($iv) === $tamanhoIv
            && $conteudoDecodificado !== false && $conteudoDecodificado !== '';
    }

    public static function acolhidoDocumentoDescriptografar($valor): string
    {
        if (!is_scalar($valor) || $valor === null) {
            return '';
        }

        $texto = trim((string)$valor);
        if ($texto === '' || !self::acolhidoDocumentoPareceCriptografado($texto) || !defined('ENCRYPTION_KEY')) {
            return $texto;
        }

        $descriptografado = Criptografia::descriptografar($texto);
        if (is_string($descriptografado)
            && (str_starts_with($descriptografado, 'Formato de criptografia')
                || str_starts_with($descriptografado, 'IV de criptografia'))) {
            throw new RuntimeException('Nao foi possivel descriptografar um documento do acolhido. Verifique a chave de criptografia configurada.');
        }
        if (!is_string($descriptografado) || in_array($descriptografado, [
            'Formato de criptografia invÃ¡lido',
            'IV de criptografia com tamanho incorreto',
            'Erro de descriptografia',
        ], true)) {
            throw new RuntimeException('NÃ£o foi possÃ­vel descriptografar um documento do acolhido. Verifique a chave de criptografia configurada.');
        }

        return trim($descriptografado);
    }

    public static function acolhidoDocumentoCriptografar($valor): ?string
    {
        if (!is_scalar($valor) || $valor === null) {
            return null;
        }

        $texto = trim((string)$valor);
        if ($texto === '') {
            return null;
        }
        if (!defined('ENCRYPTION_KEY')) {
            return $texto;
        }
        if (self::acolhidoDocumentoPareceCriptografado($texto)) {
            self::acolhidoDocumentoDescriptografar($texto);
            return $texto;
        }

        $criptografado = Criptografia::criptografar($texto);
        if (!is_string($criptografado)
            || $criptografado === ''
            || !self::acolhidoDocumentoPareceCriptografado($criptografado)
            || self::acolhidoDocumentoDescriptografar($criptografado) !== $texto) {
            throw new RuntimeException('NÃ£o foi possÃ­vel criptografar um documento do acolhido.');
        }
        return $criptografado;
    }

    public static function acolhidoDadosComplementaresParaEdicao(array $acolhido): array
    {
        $legado = self::acolhidoDadosComplementaresJsonParaLista($acolhido['dados_cadastrais_json'] ?? null);
        $atual = self::acolhidoDadosComplementaresJsonParaLista($acolhido['dados_complementares_json'] ?? null);
        $dados = array_replace_recursive($legado, $atual);
        unset($dados['transtorno_mental'], $dados['qual_transtorno']);

        if (empty($dados['documentos']) || !is_array($dados['documentos'])) {
            $dados['documentos'] = [];
            $tipoDocumento = strtoupper(trim((string)($acolhido['tipo_documento'] ?? '')));
            $numeroDocumento = self::acolhidoDocumentoDescriptografar($acolhido['documento_identificacao'] ?? '');
            $documentoLegado = match ($tipoDocumento) {
                'RG' => 'rg',
                'CT', 'CTPS' => 'ctps',
                'CERTIDAO' => 'certidao',
                'TITULO', 'TITULO ELEITORAL' => 'titulo_eleitoral',
                'SUS', 'CARTAO SUS' => 'sus',
                'CNH', 'OUTRO', 'OUTROS' => 'outros',
                default => null,
            };
            if ($documentoLegado && $numeroDocumento !== '') {
                $dados['documentos'][$documentoLegado] = ['situacao' => 'possui', 'numero' => $numeroDocumento];
            }
            $cpf = defined('ENCRYPTION_KEY') ? trim((string)Criptografia::descriptografar($acolhido['cpf'] ?? '')) : '';
            $dados['documentos']['cpf'] = ['situacao' => $cpf !== '' && stripos($cpf, 'informado') === false ? 'possui' : '', 'numero' => ''];
            $sus = self::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '');
            if ($sus !== '') {
                $dados['documentos']['sus'] = ['situacao' => 'possui', 'numero' => $sus];
            }
        }

        if (!isset($dados['documentos']['cpf']) || !is_array($dados['documentos']['cpf'])) {
            $dados['documentos']['cpf'] = ['situacao' => '', 'numero' => ''];
        }
        if (($dados['documentos']['cpf']['situacao'] ?? '') === '') {
            $cpf = defined('ENCRYPTION_KEY') ? trim((string)Criptografia::descriptografar($acolhido['cpf'] ?? '')) : '';
            if ($cpf !== '' && stripos($cpf, 'informado') === false) {
                $dados['documentos']['cpf']['situacao'] = 'possui';
            }
        }
        $dados['documentos']['cpf']['numero'] = '';

        if (!isset($dados['documentos']['sus']) || !is_array($dados['documentos']['sus'])) {
            $sus = self::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '');
            $dados['documentos']['sus'] = ['situacao' => $sus !== '' ? 'possui' : '', 'numero' => $sus];
        } elseif (trim((string)($dados['documentos']['sus']['numero'] ?? '')) === '' && self::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '') !== '') {
            $dados['documentos']['sus'] = ['situacao' => 'possui', 'numero' => self::acolhidoDocumentoDescriptografar($acolhido['cartao_sus'] ?? '')];
        }
        if (!isset($dados['documentos']['cartao_municipal']) || !is_array($dados['documentos']['cartao_municipal'])) {
            $municipal = self::acolhidoDocumentoDescriptografar($acolhido['cartao_municipal_saude'] ?? '');
            $dados['documentos']['cartao_municipal'] = ['situacao' => $municipal !== '' ? 'possui' : '', 'numero' => $municipal];
        } elseif (trim((string)($dados['documentos']['cartao_municipal']['numero'] ?? '')) === '' && self::acolhidoDocumentoDescriptografar($acolhido['cartao_municipal_saude'] ?? '') !== '') {
            $dados['documentos']['cartao_municipal'] = ['situacao' => 'possui', 'numero' => self::acolhidoDocumentoDescriptografar($acolhido['cartao_municipal_saude'] ?? '')];
        }

        foreach ($dados['documentos'] as $chaveDocumento => &$linhaDocumento) {
            if ($chaveDocumento === 'cpf' || !is_array($linhaDocumento)) {
                continue;
            }
            $linhaDocumento['numero'] = self::acolhidoDocumentoDescriptografar($linhaDocumento['numero'] ?? '');
        }
        unset($linhaDocumento);

        if (!isset($dados['beneficios']) || !is_array($dados['beneficios'])) {
            $beneficioLegado = trim((string)($acolhido['tipoBeneficio'] ?? ''));
            $dados['beneficios'] = $beneficioLegado === '' ? [] : ['outro'];
            if ($beneficioLegado !== '') {
                $dados['beneficio_outro'] = $beneficioLegado;
            }
        }

        $administracaoLegada = [
            'sim, sozinho' => 'sozinho',
            'sim, com auxílio' => 'com_auxilio',
            'não' => 'outra_pessoa',
        ];
        if (isset($administracaoLegada[$dados['administra_recursos'] ?? ''])) {
            $dados['administra_recursos'] = $administracaoLegada[$dados['administra_recursos']];
        }
        foreach (['laudo_medico', 'dst', 'orteses_proteses', 'cuidados_personalizados'] as $chaveSaude) {
            if (($dados[$chaveSaude] ?? '') === 'não') {
                $dados[$chaveSaude] = 'nao';
            }
        }
        if (is_array($dados['envolvimento_substancias'] ?? null)) {
            $substanciasLegadas = ['álcool' => 'alcool', 'outras drogas' => 'outras_drogas'];
            $dados['envolvimento_substancias'] = array_values(array_unique(array_map(static fn($item): string => $substanciasLegadas[(string)$item] ?? (string)$item, $dados['envolvimento_substancias'])));
        }

        return $dados;
    }

    public static function acolhidoDadosComplementaresFormularioParaJson(?string $jsonAtual = null): ?string
    {
        $recebidos = $_POST['dados_acolhido'] ?? [];
        if (!is_array($recebidos)) {
            throw new InvalidArgumentException('As informações complementares do acolhido são inválidas.');
        }
        $dados = self::acolhidoDadosComplementaresJsonParaLista($jsonAtual);
        unset($dados['transtorno_mental'], $dados['qual_transtorno']);
        $texto = static function (string $chave, int $limite = 5000) use ($recebidos): string {
            $valor = $recebidos[$chave] ?? '';
            if (!is_string($valor)) {
                throw new InvalidArgumentException('Um campo das informações do acolhido está inválido.');
            }
            $valor = trim($valor);
            $tamanho = function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
            if ($tamanho > $limite) {
                throw new InvalidArgumentException('Um campo das informações do acolhido excede o tamanho permitido.');
            }
            return $valor;
        };

        $documentosPost = is_array($recebidos['documentos'] ?? null) ? $recebidos['documentos'] : [];
        $documentos = [];
        if (is_array($dados['documentos']['sus'] ?? null)) {
            $documentos['sus'] = $dados['documentos']['sus'];
        }
        foreach (['certidao', 'rg', 'cpf', 'titulo_eleitoral', 'ctps', 'sus', 'cartao_municipal', 'outros'] as $chaveDocumento) {
            $linha = is_array($documentosPost[$chaveDocumento] ?? null) ? $documentosPost[$chaveDocumento] : [];
            $situacao = is_string($linha['situacao'] ?? null) ? $linha['situacao'] : '';
            if ($situacao !== '' && !in_array($situacao, ['possui', 'nao_possui', 'nao_sabe_informar', 'nao_disponibilizado'], true)) {
                throw new InvalidArgumentException('Selecione uma situação válida para os documentos pessoais.');
            }
            $numero = is_string($linha['numero'] ?? null) ? trim($linha['numero']) : '';
            $tamanhoNumero = function_exists('mb_strlen') ? mb_strlen($numero, 'UTF-8') : strlen($numero);
            if ($tamanhoNumero > 100) {
                throw new InvalidArgumentException('O número de um documento excede 100 caracteres.');
            }
            if ($chaveDocumento === 'cpf') {
                $numero = '';
            } elseif ($situacao !== 'possui') {
                $numero = '';
            }
            $documentos[$chaveDocumento] = [
                'situacao' => $situacao,
                'numero' => $chaveDocumento === 'cpf' || $situacao !== 'possui'
                    ? ''
                    : (self::acolhidoDocumentoCriptografar($numero) ?? ''),
            ];
        }
        $dados['documentos'] = $documentos;

        foreach (['orientacao_sexual' => 80, 'orientacao_sexual_outros' => 300] as $chave => $limite) {
            $dados[$chave] = $texto($chave, $limite);
        }

        $beneficiosRecebidos = $recebidos['beneficios'] ?? [];
        if (!is_array($beneficiosRecebidos)) {
            throw new InvalidArgumentException('A seleção de benefícios está inválida.');
        }
        $beneficiosPermitidos = ['nenhum', 'aposentadoria', 'pensao', 'bpc', 'programa_social', 'outro'];
        $beneficios = array_values(array_unique(array_filter($beneficiosRecebidos, static fn($item): bool => is_string($item) && in_array($item, $beneficiosPermitidos, true))));
        if (in_array('nenhum', $beneficios, true)) {
            $beneficios = ['nenhum'];
        }
        $atividade = $texto('atividade_remunerada', 10);
        if (!in_array($atividade, ['', 'sim', 'nao'], true)) {
            throw new InvalidArgumentException('Informe se o acolhido exerce atividade remunerada.');
        }
        if ($atividade === 'sim' && !in_array('atividade', $beneficios, true)) {
            $beneficios[] = 'atividade';
        }
        if ($atividade !== 'sim') {
            $beneficios = array_values(array_diff($beneficios, ['atividade']));
        }
        $dados['beneficios'] = $beneficios;
        $dados['atividade_remunerada'] = $atividade;
        $dados['beneficio_atividade'] = $texto('beneficio_atividade');
        $dados['beneficio_outro'] = $texto('beneficio_outro');
        $dados['outras_fontes_renda'] = $texto('outras_fontes_renda');
        $administracao = $texto('administra_recursos', 40);
        if (!in_array($administracao, ['', 'sozinho', 'com_auxilio', 'outra_pessoa'], true)) {
            throw new InvalidArgumentException('Selecione uma forma válida de administração da renda.');
        }
        $dados['administra_recursos'] = $administracao;
        $dados['responsavel_recursos'] = $texto('responsavel_recursos', 1000);

        foreach (['laudo_medico', 'dst', 'orteses_proteses', 'cuidados_personalizados'] as $chaveSaude) {
            $valorSaude = $texto($chaveSaude, 10);
            if (!in_array($valorSaude, ['', 'sim', 'nao'], true)) {
                throw new InvalidArgumentException('Informe uma resposta válida nos dados de saúde.');
            }
            $dados[$chaveSaude] = $valorSaude;
        }
        foreach (['qual_dst', 'especialistas_tratamentos', 'especialistas_necessarios', 'qual_orteses_proteses', 'qual_cuidados_personalizados', 'envolvimento_outros'] as $chaveTextoSaude) {
            $dados[$chaveTextoSaude] = $texto($chaveTextoSaude);
        }
        $envolvimento = $recebidos['envolvimento_substancias'] ?? [];
        if (!is_array($envolvimento)) {
            throw new InvalidArgumentException('A seleção de substâncias está inválida.');
        }
        $envolvimentoPermitido = ['alcool', 'tabaco', 'outras_drogas', 'outros'];
        $dados['envolvimento_substancias'] = array_values(array_unique(array_filter($envolvimento, static fn($item): bool => is_string($item) && in_array($item, $envolvimentoPermitido, true))));

        $json = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            throw new InvalidArgumentException('Não foi possível salvar as informações complementares.');
        }
        return $json;
    }

    public static function acolhidoDocumentoLegado(array $dados): array
    {
        $documentos = is_array($dados['documentos'] ?? null) ? $dados['documentos'] : [];
        foreach (['rg' => 'RG', 'ctps' => 'CT', 'outros' => 'OUTRO', 'certidao' => 'CERTIDAO', 'titulo_eleitoral' => 'TITULO', 'sus' => 'SUS'] as $chave => $tipo) {
            $linha = $documentos[$chave] ?? [];
            if (($linha['situacao'] ?? '') === 'possui' && trim((string)($linha['numero'] ?? '')) !== '') {
                $numero = self::acolhidoDocumentoDescriptografar($linha['numero']);
                return [$tipo, function_exists('mb_substr') ? mb_substr($numero, 0, 100, 'UTF-8') : substr($numero, 0, 100)];
            }
        }
        return [null, null];
    }

    public static function acolhidoResumoBeneficios(array $dados): ?string
    {
        $nomes = ['nenhum' => 'Não possui benefício social', 'aposentadoria' => 'Aposentadoria', 'pensao' => 'Pensão', 'bpc' => 'BPC/LOAS', 'programa_social' => 'Programa social', 'atividade' => 'Atividade remunerada', 'outro' => 'Outro benefício ou renda'];
        $selecionados = is_array($dados['beneficios'] ?? null) ? $dados['beneficios'] : [];
        $partes = array_values(array_map(static fn(string $item): string => $nomes[$item], array_filter($selecionados, static fn($item): bool => is_string($item) && isset($nomes[$item]))));
        foreach (['beneficio_atividade', 'beneficio_outro', 'outras_fontes_renda'] as $chave) {
            $valor = trim((string)($dados[$chave] ?? ''));
            if ($valor !== '') $partes[] = $valor;
        }
        if (!$partes) {
            return null;
        }
        $resumo = implode('; ', array_unique($partes));
        return function_exists('mb_substr') ? mb_substr($resumo, 0, 45, 'UTF-8') : substr($resumo, 0, 45);
    }

    public static function acolhidoDadosComplementaresGrupos(array $dados): array
    {
        $grupos = [];
        $documentos = is_array($dados['documentos'] ?? null) ? $dados['documentos'] : [];
        $rotulosDocumentos = [
            'certidao' => 'Certidão de nascimento e/ou casamento', 'rg' => 'RG', 'cpf' => 'CPF',
            'titulo_eleitoral' => 'Título eleitoral', 'ctps' => 'CTPS', 'sus' => 'Cartão Nacional do SUS',
            'cartao_municipal' => 'Cartão Municipal de Saúde', 'outros' => 'Outros documentos',
        ];
        $rotulosSituacao = ['possui' => 'Possui', 'nao_possui' => 'Não possui', 'nao_sabe_informar' => 'Não sabe informar', 'nao_disponibilizado' => 'Possui, mas não está disponível no momento'];
        foreach ($rotulosDocumentos as $chave => $rotulo) {
            $linha = is_array($documentos[$chave] ?? null) ? $documentos[$chave] : [];
            $situacao = (string)($linha['situacao'] ?? '');
            $numero = trim((string)($linha['numero'] ?? ''));
            $grupos['Documentos pessoais'][$rotulo] = ($rotulosSituacao[$situacao] ?? 'Não informado') . ($situacao === 'possui' && $numero !== '' ? ' — ' . $numero : '');
        }

        $mapas = [
            'Dados pessoais adicionais' => ['orientacao_sexual' => 'Orientação sexual', 'orientacao_sexual_outros' => 'Outra orientação sexual'],
            'Benefícios, renda e atividade' => [
                'beneficios' => 'Benefícios sociais ou previdenciários', 'atividade_remunerada' => 'Atividade remunerada',
                'beneficio_atividade' => 'Atividade ou fonte de renda', 'beneficio_outro' => 'Outro benefício',
                'outras_fontes_renda' => 'Outras fontes de renda ou doações', 'administra_recursos' => 'Administração da renda',
                'responsavel_recursos' => 'Pessoa que auxilia ou administra a renda',
            ],
            'Saúde e acompanhamento' => [
                'laudo_medico' => 'Possui laudo médico?',
                'dst' => 'Possui alguma IST?', 'qual_dst' => 'Qual IST?',
                'restricao_alimentar' => 'Possui restrição alimentar?', 'qual_restricao' => 'Qual restrição alimentar?',
                'doencas_cronicas' => 'Doenças crônicas', 'comorbidades' => 'Comorbidades',
                'especialistas_tratamentos' => 'Especialistas com quem já se tratou', 'especialistas_necessarios' => 'Especialistas de que necessita atualmente',
                'orteses_proteses' => 'Necessita de órteses ou próteses?', 'qual_orteses_proteses' => 'Órteses ou próteses necessárias',
                'cuidados_personalizados' => 'Necessita de cuidados personalizados?', 'qual_cuidados_personalizados' => 'Cuidados personalizados',
                'envolvimento_substancias' => 'Indícios de envolvimento com', 'envolvimento_outros' => 'Outras situações relacionadas',
            ],
        ];
        $rotulosValores = [
            'nenhum' => 'Não possui benefício', 'aposentadoria' => 'Aposentadoria', 'pensao' => 'Pensão', 'bpc' => 'BPC/LOAS',
            'programa_social' => 'Programa social ou transferência de renda', 'atividade' => 'Atividade remunerada', 'outro' => 'Outro benefício',
            'heterossexual' => 'Heterossexual', 'homossexual' => 'Homossexual', 'bissexual' => 'Bissexual', 'pansexual' => 'Pansexual',
            'assexual' => 'Assexual', 'outros' => 'Outros', 'prefere_nao_informar' => 'Prefere não informar',
            'sim' => 'Sim', 'nao' => 'Não', 'sozinho' => 'Administra sozinho', 'com_auxilio' => 'Administra com auxílio',
            'outra_pessoa' => 'Outra pessoa administra', 'alcool' => 'Álcool', 'tabaco' => 'Tabaco', 'outras_drogas' => 'Outras drogas',
        ];
        foreach ($mapas as $titulo => $campos) {
            foreach ($campos as $chave => $rotulo) {
                $valor = $dados[$chave] ?? null;
                if (is_array($valor)) {
                    $valor = implode(', ', array_map(static fn($item): string => $rotulosValores[(string)$item] ?? (string)$item, $valor));
                } elseif (is_scalar($valor)) {
                    $valor = $chave === 'orientacao_sexual' && $valor === 'outros'
                        ? 'Outra'
                        : ($rotulosValores[(string)$valor] ?? trim((string)$valor));
                } else {
                    $valor = '';
                }
                if ($valor !== '') {
                    $grupos[$titulo][$rotulo] = $valor;
                }
            }
        }
        return array_filter($grupos, static fn(array $grupo): bool => count($grupo) > 0);
    }
}
