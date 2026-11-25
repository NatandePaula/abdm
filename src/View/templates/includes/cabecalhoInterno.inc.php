<?php
$controleAcesso ??= new \Abdm\Infraestrutura\ControleAcesso($pdo);
$controleAcesso->exigirUsuarioAutenticado();
[
    'mostrarMenuInformacoes' => $mostrarMenuInformacoes,
    'linksMenuInformacoes' => $linksMenuInformacoes,
    'nomeUsuarioNav' => $nomeUsuarioNav,
] = (new \Abdm\Controller\MenuInformacoesController($pdo))->cabecalho($controleAcesso);

?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="auto">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="csrf-token" content="<?php echo htmlspecialchars(\Abdm\Infraestrutura\Sessao::tokenCsrf(), ENT_QUOTES, 'UTF-8'); ?>" />

    <title>ABDM</title>
    <!-- Bootstrap e ícones -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/dist/css/bootstrap.min.css">

    <!-- CDN do Bootstrap Icons -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/bootstrap-icons/bootstrap-icons.min.css">

    <!-- Alternância entre os modos de cor -->
    <script src="<?php echo BASE_URL; ?>assets/js/color-modes.js"></script>

    <link href="<?php echo BASE_URL; ?>/sign-in.css" rel="stylesheet" />
    
    <link href="<?php echo BASE_URL; ?>assets/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <meta name="theme-color" content="#712cf9" />
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: #0000001a;
            border: solid rgba(0, 0, 0, 0.15);
            border-width: 1px 0;
            box-shadow:
                inset 0 0.5em 1.5em #0000001a,
                inset 0 0.125em 0.5em #00000026;
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -0.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;
            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        .bd-mode-toggle .bi {
            width: 1em;
            height: 1em;
        }

        .bd-mode-toggle .dropdown-menu .active .bi {
            display: block !important;
        }

        .form-control-dark {
            border-color: var(--bs-gray);
        }

        .form-control-dark:focus {
            border-color: #fff;
            box-shadow: 0 0 0 .25rem rgba(255, 255, 255, .25);
        }

        .text-small {
            font-size: 85%;
        }

        .dropdown-toggle:not(:focus) {
            outline: 0;
        }
        
        /* CSS para a troca da Logo */
        .logo-dark {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-light {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-dark {
            display: block !important;
        }
    </style>
    <?php if (!empty($usarEstilosImpressao)): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/print_styles.css" media="print">
    <?php endif; ?>
</head>

<body>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="check2" viewBox="0 0 16 16">
            <path
                d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z">
            </path>
        </symbol>
        <symbol id="circle-half" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"></path>
        </symbol>
        <symbol id="moon-stars-fill" viewBox="0 0 16 16">
            <path
                d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z">
            </path>
            <path
                d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z">
            </path>
        </symbol>
        <symbol id="sun-fill" viewBox="0 0 16 16">
            <path
                d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z">
            </path>
        </symbol>
    </svg>
    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
        <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button"
            aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
            <svg class="bi my-1 theme-icon-active" aria-hidden="true">
                <use href="#circle-half"></use>
            </svg>
            <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light"
                    aria-pressed="false">
                    <svg class="bi me-2 opacity-50" aria-hidden="true">
                        <use href="#sun-fill"></use>
                    </svg>
                    Light
                    <svg class="bi ms-auto d-none" aria-hidden="true">
                        <use href="#check2"></use>
                    </svg>
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"
                    aria-pressed="false">
                    <svg class="bi me-2 opacity-50" aria-hidden="true">
                        <use href="#moon-stars-fill"></use>
                    </svg>
                    Dark
                    <svg class="bi ms-auto d-none" aria-hidden="true">
                        <use href="#check2"></use>
                    </svg>
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto"
                    aria-pressed="true">
                    <svg class="bi me-2 opacity-50" aria-hidden="true">
                        <use href="#circle-half"></use>
                    </svg>
                    Auto
                    <svg class="bi ms-auto d-none" aria-hidden="true">
                        <use href="#check2"></use>
                    </svg>
                </button>
            </li>
        </ul>
    </div>

    <header class="p-3 mb-3 border-bottom">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="<?php echo BASE_URL; ?>views/home.php"
                    class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                    <img class="bi me-2" width="200" height="45" role="img" aria-label="Logo" src="<?php echo BASE_URL; ?>assets/imagens/Logo2 colorida.png">

                    </img>
                </a>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li>
                        <a href="<?php echo BASE_URL; ?>views/home.php" class="nav-link px-2 link-body-emphasis">Inicio</a>
                    </li>
                    <?php if ($controleAcesso->usuarioPode('calendario') || $controleAcesso->usuarioPode('gerenciar_calendario')): ?>
                    <li>
                        <div class="d-flex align-items-center">
                            <?php if ($controleAcesso->usuarioPode('calendario')): ?>
                                <a href="<?php echo BASE_URL; ?>views/calendario.php" class="nav-link px-2 link-body-emphasis">Calendário</a>
                            <?php endif; ?>
                            <?php if ($controleAcesso->usuarioPode('gerenciar_calendario')): ?>
                                <a class="nav-link px-1 link-body-emphasis" href="<?php echo BASE_URL; ?>views/gerenciar_calendario.php" aria-label="Gerenciar calendário" title="Gerenciar calendário"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if ($controleAcesso->usuarioPode('acolhidos') || $controleAcesso->usuarioPode('pia') || $controleAcesso->usuarioPode('registrar_evolucao_acolhido') || $controleAcesso->usuarioPode('consultar_minhas_evolucoes') || $controleAcesso->usuarioPode('consultar_todas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucao_acolhido') || $controleAcesso->usuarioPode('imprimir_minhas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucoes_profissional') || $controleAcesso->usuarioPode('imprimir_evolucoes_todos') || $controleAcesso->usuarioPode('saude_acolhido') || $controleAcesso->usuarioPode('gerenciar_pia_campos')): ?>
                    <li>
                        <div class="dropdown text-end">
                            
                            <a href="#" class="nav-link px-2 link-body-emphasis"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Acolhidos
                            </a>
                            
                            <ul class="dropdown-menu text-small">
                                <?php if ($controleAcesso->usuarioPode('acolhidos')): ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/acolhidos.php">Ver colhidos</a></li>
                                
                                <?php endif; ?>
                                <?php if ($controleAcesso->usuarioPode('pia')): ?><li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/pia.php">PIA</a></li><?php endif; ?>
                                <?php if ($controleAcesso->usuarioPode('registrar_evolucao_acolhido') || $controleAcesso->usuarioPode('consultar_minhas_evolucoes') || $controleAcesso->usuarioPode('consultar_todas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucao_acolhido') || $controleAcesso->usuarioPode('imprimir_minhas_evolucoes') || $controleAcesso->usuarioPode('imprimir_evolucoes_profissional') || $controleAcesso->usuarioPode('imprimir_evolucoes_todos')): ?><li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/evolucoes.php">Evolução</a></li><?php endif; ?>
                                <?php if ($controleAcesso->usuarioPode('saude_acolhido')): ?><li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/saude_acolhido.php">Saúde</a></li><?php endif; ?>
                               
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if ($controleAcesso->usuarioPode('dispensacao') || $controleAcesso->usuarioPode('estoque') || $controleAcesso->usuarioPode('prescricoes')): ?>
                    <li>
                        <div class="dropdown text-end">
                            
                            <a href="#" class="nav-link px-2 link-body-emphasis"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Farmácia
                            </a>
                            
                            <ul class="dropdown-menu text-small">
                                <?php if ($controleAcesso->usuarioPode('prescricoes')): ?><li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/prescricoes.php">Prescrições</a></li><?php endif; ?>
                                <?php if ($controleAcesso->usuarioPode('dispensacao')): ?><li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/dispensacao.php">Quadro de Dispensação</a></li><?php endif; ?>
                                <?php if ($controleAcesso->usuarioPode('estoque')): ?>
                                    <?php if ($controleAcesso->usuarioPode('dispensacao') || $controleAcesso->usuarioPode('prescricoes')): ?><hr class="dropdown-divider" /><?php endif; ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/medicamentos_estoque.php">Gestão de Estoque</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/cadastrar_medicamento.php">Cadastrar Novo Medicamento</a></li>
                                <?php endif; ?>
                                
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                    <?php if ($controleAcesso->usuarioPode('relatorio_diario')): ?>
                    <li><a href="<?php echo BASE_URL; ?>views/relatorio.php" class="nav-link px-2 link-body-emphasis">Relatório</a></li>
                    <?php endif; ?>
                    <?php if ($mostrarMenuInformacoes): ?>
                    <li>
                        <div class="d-flex align-items-center">
                            <div class="dropdown text-end">
                                <a href="#" class="nav-link px-2 link-body-emphasis" data-bs-toggle="dropdown" aria-expanded="false">Informações</a>
                                <ul class="dropdown-menu text-small">
                                    <?php foreach ($linksMenuInformacoes as $linkInformacao): ?>
                                        <li><a class="dropdown-item" href="<?php echo htmlspecialchars($linkInformacao['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($linkInformacao['titulo'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                                    <?php endforeach; ?>
                                    <?php if (empty($linksMenuInformacoes)): ?>
                                        <li><span class="dropdown-item-text text-body-secondary">Nenhum link cadastrado</span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php if ($controleAcesso->usuarioPode('gerenciar_informacoes')): ?>
                                <a class="nav-link px-1 link-body-emphasis" href="<?php echo BASE_URL; ?>views/gerenciar_informacoes.php" aria-label="Gerenciar links de informações" title="Gerenciar links de informações"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['is_admin'])): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>views/usuarios.php" class="nav-link px-2 link-body-emphasis">Usuários</a>
                    </li>
                    <?php endif; ?>

                </ul>

                <div class="d-flex flex-column align-items-end">
                    <div class="dropdown text-end">
                    <a href="#" class="d-flex align-items-center gap-2 link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <span class="fw-semibold"><?php echo htmlspecialchars($nomeUsuarioNav, ENT_QUOTES, 'UTF-8'); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                            class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                    </a>
                    <ul class="dropdown-menu text-small">
                        
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/minha_senha.php">Trocar minha senha</a></li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <form method="post" action="<?= BASE_URL ?>dao/logout.php" id="formEncerrarSessao">
                                <?= \Abdm\Infraestrutura\Sessao::campoTokenCsrf() ?>
                                <button class="dropdown-item" type="submit">Sair</button>
                            </form>
                        </li>
                    </ul>
                    </div>
                    <small class="text-body-secondary mt-1">Sua sessão expira em: <span data-session-timer>00:30</span></small>
                </div>
            </div>
        </div>
    </header>

<?php
// Exibe a mensagem flash, se houver
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    echo '<div class="container mt-4"><div class="alert alert-' . htmlspecialchars($message['type']) . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div></div>';
    unset($_SESSION['flash_message']);
}
?>
