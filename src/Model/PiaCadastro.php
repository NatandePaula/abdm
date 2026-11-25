<?php

namespace Abdm\Model;

use DateTimeImmutable;
use PDO;
use InvalidArgumentException;

final class PiaCadastro
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function piaCamposCadastroJaPresentesNoFormularioPrincipal(): array
    {
        return ['beneficios', 'composicao_familiar', 'cid10', 'motivo_acolhimento', 'cartao_municipal_saude'];
    }

    public function acolhidoDadosPiaFormularioParaJson(?string $jsonAtual = null): ?string
    {
        $recebidos = $_POST['dados_cadastrais'] ?? [];
        if (!is_array($recebidos)) {
            throw new InvalidArgumentException('Os campos de cadastro do PIA são inválidos.');
        }
        $campos = $this->pdo->query("SELECT chave, tipo, opcoes, rotulo FROM pia_campos WHERE ativo = 1 AND origem = 'cadastro'")->fetchAll(PDO::FETCH_ASSOC);
        $dadosAtuais = is_string($jsonAtual) && $jsonAtual !== '' ? json_decode($jsonAtual, true) : [];
        $permitidos = is_array($dadosAtuais) ? $dadosAtuais : [];
        foreach ($campos as $campo) {
            $chave = (string)$campo['chave'];
            if (in_array($chave, self::piaCamposCadastroJaPresentesNoFormularioPrincipal(), true)) continue;
            if ($chave === 'data_nascimento') continue; // Mantido na coluna dataNascimento do acolhido.
            if (!array_key_exists($chave, $recebidos)) continue;
            $tipo = (string)$campo['tipo'];
            $valor = $recebidos[$chave];
            if (in_array($tipo, ['text', 'textarea', 'date', 'select'], true)) {
                if (!is_string($valor)) {
                    throw new InvalidArgumentException('O campo "' . $campo['rotulo'] . '" está inválido.');
                }
                $valor = trim($valor);
                $tamanho = function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
                if ($tamanho > 20000) {
                    throw new InvalidArgumentException('O campo "' . $campo['rotulo'] . '" excede o tamanho permitido.');
                }
                if ($tipo === 'date' && $valor !== '') {
                    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
                    if (!$data || $data->format('Y-m-d') !== $valor) {
                        throw new InvalidArgumentException('Informe uma data válida no campo "' . $campo['rotulo'] . '".');
                    }
                }
                if ($tipo === 'select' && $valor !== '' && !in_array($valor, array_map('trim', explode('|', (string)$campo['opcoes'])), true)) {
                    throw new InvalidArgumentException('A opção escolhida no campo "' . $campo['rotulo'] . '" não é válida.');
                }
                $permitidos[$chave] = $valor;
                continue;
            }
            if (!is_array($valor)) {
                throw new InvalidArgumentException('O campo "' . $campo['rotulo'] . '" está inválido.');
            }
            $rotuloCampo = (string)$campo['rotulo'];
            $limparArray = static function ($item, int $profundidade = 0) use (&$limparArray, $rotuloCampo) {
                if ($profundidade > 5) throw new InvalidArgumentException('A estrutura de um campo cadastral excede o limite permitido.');
                if (is_array($item)) {
                    $saida = [];
                    foreach ($item as $subchave => $subvalor) {
                        if (!is_int($subchave) && !is_string($subchave)) continue;
                        $saida[$subchave] = $limparArray($subvalor, $profundidade + 1);
                    }
                    return $saida;
                }
                if (is_string($item)) {
                    if (preg_match('//u', $item) !== 1 || (function_exists('mb_strlen') ? mb_strlen($item, 'UTF-8') : strlen($item)) > 20000) {
                        throw new InvalidArgumentException('Há conteúdo inválido ou longo demais no campo "' . $rotuloCampo . '".');
                    }
                    return trim($item);
                }
                if (is_int($item) || is_float($item) || is_bool($item) || $item === null) return $item;
                throw new InvalidArgumentException('O campo "' . $rotuloCampo . '" contém um valor inválido.');
            };
            $permitidos[$chave] = $limparArray($valor);
        }
        $json = json_encode($permitidos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false || strlen($json) > 500000) {
            throw new InvalidArgumentException('Os campos adicionais do cadastro excedem o limite permitido.');
        }
        return $json;
    }
}
