<?php

namespace Abdm\View;

final class RodapeImpressao
{
    public static function rodapeImpressaoPadrao(string $nomeImpressor, string $contexto = ''): string
    {
        $nomeSeguro = htmlspecialchars($nomeImpressor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $contextoSeguro = trim($contexto) !== ''
            ? htmlspecialchars(trim($contexto), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' · '
            : '';

        return '<table width="100%" style="border-top:1px solid #ccc;font-size:9pt;color:#555;font-family:sans-serif;">'
            . '<tr><td width="100%" align="center" style="padding-top:5px;">'
            . $contextoSeguro . 'Impresso por ' . $nomeSeguro . ' · {DATE d/m/Y H:i:s} | Página {PAGENO} de {nbpg}'
            . '</td></tr>'
            . '<tr><td align="center" style="padding-top:5px;"><img src="var:rodapeimg" style="width:100%;" /></td></tr>'
            . '</table>';
    }
}
