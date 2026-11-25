<?php

namespace Abdm\Controller;

use Abdm\Infraestrutura\ControleAcesso;

final class CatalogoMedicamentosController
{
    public function __construct(private readonly ControleAcesso $controleAcesso)
    {
    }

    /**
     * Normaliza uma string para busca: remove acentos e a torna maiúscula.
     */
    private static function normalizarTextoParaBusca($str)
    {
        $map = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ç' => 'c', 'Ç' => 'C', 'ñ' => 'n', 'Ñ' => 'N',
        ];
        $str = strtr($str, $map);
        return mb_strtoupper($str, 'UTF-8');
    }

    /**
     * Reduz o texto da apresentação, removendo detalhes de embalagem.
     */
    private static function encurtarApresentacao($apresentacao)
    {
        // Delimitadores que indicam o início de informações de embalagem.
        $delimitadores = [
            ' CT ', ' CX ', ' FR ', ' BG ', ' BL ', ' ENV ', ' AMP ', ' FA ', ' PLAS ', ' VD ', ' AL ', ' OPC ', ' INC ', ' X '
        ];

        $posicaoCorte = mb_strlen($apresentacao, 'UTF-8');

        foreach ($delimitadores as $del) {
            $pos = mb_strpos($apresentacao, $del, 0, 'UTF-8');
            if ($pos !== false && $pos < $posicaoCorte) {
                $posicaoCorte = $pos;
            }
        }
        return trim(mb_substr($apresentacao, 0, $posicaoCorte, 'UTF-8'));
    }

    public function executar(): void
    {
        ini_set('memory_limit', '512M');
        header('Content-Type: application/json; charset=utf-8');
        $this->controleAcesso->exigirPermissao('estoque');

        $caminhoCsv = ROOT_DIR . '/resources/xls_conformidade_site_20260610_121627707.csv';

        if (!file_exists($caminhoCsv)) {
            http_response_code(500);
            echo json_encode(['erro' => 'Arquivo de dados de medicamentos não encontrado.']);
            exit;
        }

        $resultados = [];
        $unicos = [];

        $handle = fopen($caminhoCsv, 'r');
        if ($handle === FALSE) {
            http_response_code(500);
            echo json_encode(['erro' => 'Não foi possível abrir o arquivo de dados.']);
            exit;
        }

        $primeiraLinha = fgets($handle);
        $delimitador = is_string($primeiraLinha) && str_contains($primeiraLinha, ';') ? ';' : ',';
        rewind($handle);
        $cabecalho = fgetcsv($handle, 0, $delimitador, '"', '\\');
        if ($cabecalho === false) {
            fclose($handle);
            http_response_code(500);
            echo json_encode(['erro' => 'O catálogo de medicamentos está vazio.']);
            exit;
        }

        $converter = function($str) {
            return (!mb_check_encoding($str, 'UTF-8')) ? mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1') : $str;
        };

        $cabecalhoUtf8 = array_map($converter, $cabecalho);
        $colunasFlip = array_flip(array_map('strtoupper', $cabecalhoUtf8));

        $idxPrincipio = $colunasFlip['SUBSTÂNCIA'] ?? -1;
        $idxNome = $colunasFlip['PRODUTO'] ?? -1;
        $idxApresentacao = $colunasFlip['APRESENTAÇÃO'] ?? -1;

        if ($idxPrincipio === -1 || $idxNome === -1 || $idxApresentacao === -1) {
            http_response_code(500);
            echo json_encode(['erro' => 'Colunas essenciais (SUBSTÂNCIA, PRODUTO, APRESENTAÇÃO) não encontradas no CSV.']);
            exit;
        }

        while (($data = fgetcsv($handle, 0, $delimitador, '"', '\\')) !== false) {
            if (isset($data[$idxPrincipio]) && isset($data[$idxNome])) {
                $principioAtivoUtf8 = $converter(trim($data[$idxPrincipio]));
                $nomeProdutoUtf8 = $converter(trim($data[$idxNome]));
                $apresentacaoUtf8 = ($idxApresentacao !== -1 && isset($data[$idxApresentacao])) ? $converter(trim($data[$idxApresentacao])) : '';

                $principioAtivoNormalizado = self::normalizarTextoParaBusca($principioAtivoUtf8);
                $nomeProdutoNormalizado = self::normalizarTextoParaBusca($nomeProdutoUtf8);

                $chaveUnica = $nomeProdutoNormalizado . '|' . $principioAtivoNormalizado;

                if (!isset($unicos[$chaveUnica])) {
                    $apresentacaoCurta = self::encurtarApresentacao($apresentacaoUtf8);

                    $apresentacaoUpper = mb_strtoupper($apresentacaoUtf8, 'UTF-8'); // Usa a string original para a lógica de sugestão, que é mais robusta
                    $unidadeSugerida = 'unidade';
                    if (mb_strpos($apresentacaoUpper, 'CAP') !== false || mb_strpos($apresentacaoUpper, 'CÁPSULA') !== false) {
                        $unidadeSugerida = 'capsula';
                    } elseif (mb_strpos($apresentacaoUpper, 'COM') !== false || mb_strpos($apresentacaoUpper, 'DRG') !== false || mb_strpos($apresentacaoUpper, 'COMPRIMIDO') !== false) {
                        $unidadeSugerida = 'comprimido';
                    } elseif (mb_strpos($apresentacaoUpper, 'ML') !== false || mb_strpos($apresentacaoUpper, 'SOL') !== false || mb_strpos($apresentacaoUpper, 'XPE') !== false || mb_strpos($apresentacaoUpper, 'SUS') !== false || mb_strpos($apresentacaoUpper, 'GOTAS') !== false) {
                        $unidadeSugerida = 'ml';
                    }

                    $resultados[] = [
                        'principio_ativo' => $principioAtivoUtf8,
                        'nome_produto' => $nomeProdutoUtf8,
                        'apresentacao' => $apresentacaoCurta,
                        'unidade_base_sugerida' => $unidadeSugerida,
                        'search_pa' => $principioAtivoNormalizado,
                        'search_np' => $nomeProdutoNormalizado
                    ];
                    $unicos[$chaveUnica] = true;
                }
            }
        }
        fclose($handle);

        echo json_encode($resultados);
    }
}
