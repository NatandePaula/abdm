<?php

namespace Abdm\View;

use DateTimeImmutable;

final class TextoRelatorio
{
    public static function relatorioDataFiltroValida(mixed $valor, string $dataHoje): bool
    {
        if (!is_string($valor) || str_contains($valor, "\0")) {
            return false;
        }
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        $erros = DateTimeImmutable::getLastErrors();
        return $data !== false
            && $data->format('Y-m-d') === $valor
            && (!is_array($erros) || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
            && $valor <= $dataHoje;
    }

    public static function formatarConteudoRelatorioHtml(string $conteudo, array $mencoes = [], string $termoBusca = ''): string
    {
        $escaparTrecho = static function (string $trecho) use ($termoBusca): string {
            if ($termoBusca === '') {
                return htmlspecialchars($trecho, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            $partes = preg_split('/(' . preg_quote($termoBusca, '/') . ')/iu', $trecho, -1, PREG_SPLIT_DELIM_CAPTURE);
            if (!is_array($partes)) {
                return htmlspecialchars($trecho, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            $html = '';
            foreach ($partes as $indice => $parte) {
                $parteSegura = htmlspecialchars($parte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $html .= $indice % 2 === 1 ? '<mark>' . $parteSegura . '</mark>' : $parteSegura;
            }
            return $html;
        };

        $nomes = [];
        foreach ($mencoes as $mencao) {
            $nome = is_array($mencao) ? ($mencao['nome'] ?? '') : $mencao;
            if (is_string($nome) && trim($nome) !== '') {
                $nomes[] = trim($nome);
            }
        }
        $nomes = array_values(array_unique($nomes));
        usort($nomes, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        if (!$nomes) {
            return nl2br($escaparTrecho($conteudo), false);
        }

        $alternativas = implode('|', array_map(static fn(string $nome): string => preg_quote($nome, '/'), $nomes));
        $padraoMencao = '/(?<![\p{L}\p{N}_])@(?:' . $alternativas . ')(?![\p{L}\p{N}_])/iu';
        $quantidade = preg_match_all($padraoMencao, $conteudo, $correspondencias, PREG_OFFSET_CAPTURE);
        if ($quantidade === false || $quantidade === 0) {
            return nl2br($escaparTrecho($conteudo), false);
        }

        $html = '';
        $posicaoAtual = 0;
        foreach ($correspondencias[0] as [$mencaoTexto, $posicao]) {
            $html .= $escaparTrecho(substr($conteudo, $posicaoAtual, $posicao - $posicaoAtual));
            $html .= '<strong>' . $escaparTrecho($mencaoTexto) . '</strong>';
            $posicaoAtual = $posicao + strlen($mencaoTexto);
        }
        $html .= $escaparTrecho(substr($conteudo, $posicaoAtual));

        return nl2br($html, false);
    }
}
