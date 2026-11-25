<?php

require_once ROOT_DIR . '/src/View/templates/includes/cabecalho.inc.php';
?>

    <main class="form-signin w-100 m-auto">
      <form method="POST" autocomplete="off">
        <?php echo \Abdm\Infraestrutura\Sessao::campoTokenCsrf(); ?>
        
        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger text-center mb-3" role="alert">
                <?php echo $mensagemErro; ?>
            </div>
        <?php endif; ?>

        <div class="form-floating text-center">
          <img
            class="mb-1 rounded"
            src="<?php echo BASE_URL; ?>assets/imagens/Logo1 colorida.png"
            alt="Logo"
            width="200"
            height="200"
            id="logo-login" 
          />
        </div>
        <h1 class="h3 mb-3 text-center">Faça seu login</h1>
        
        <div class="form-floating">
          <input
            type="text"
            class="form-control"
            id="floatingInput"
            placeholder="Nome de usuário"
            name="username"
            required
            autocomplete="off" 
          />
          <label for="floatingInput">Nome de usuário</label>
        </div>
        
        <div class="form-floating position-relative">
          <input
            type="text" 
            class="form-control pe-5"
            id="senhaCampo"
            placeholder="Senha"
            name="password"
            required
            autocomplete="new-password"
            disabled 
          />
          <label for="senhaCampo">Senha</label>
          
          <button
            type="button"
            class="btn bg-transparent border-0 shadow-none position-absolute p-2 text-secondary"
            style="right: 8px; top: 50%; transform: translateY(-50%); z-index: 5; line-height: 1;"
            id="togglePassword"
            aria-label="Mostrar senha"
            title="Mostrar senha"
          >
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
                <circle cx="12" cy="12" r="3"/>
                <path id="eyeSlash" d="m3 3 18 18"/>
            </svg>
          </button>
        </div>
        
        <button class="btn btn-primary w-100 py-2" type="submit">
          Entrar
        </button>
        
      </form>
    </main>

<?php

require_once ROOT_DIR . '/src/View/templates/includes/rodape.inc';
?>
