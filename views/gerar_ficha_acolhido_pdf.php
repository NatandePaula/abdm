<?php

require_once dirname(__DIR__) . '/config/aplicacao.php';

header_register_callback(static function (): void {
    header('Cache-Control: private, no-store');
    header('Pragma: no-cache');
});

require ROOT_DIR . '/src/View/templates/gerar_ficha_acolhido_pdf.php';
