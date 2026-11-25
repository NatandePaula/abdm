
<!--desativar salvamento de senhas, e ativar o botão de visualização de senha digitada-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('senhaCampo');
        const toggleButton = document.getElementById('togglePassword');
        const eyeSlash = document.getElementById('eyeSlash');

        const atualizarControleSenha = function() {
            if (!passwordField || !toggleButton) return;

            const senhaVisivel = passwordField.type === 'text';
            if (eyeSlash) eyeSlash.style.display = senhaVisivel ? 'none' : 'inline';

            const rotulo = senhaVisivel ? 'Ocultar senha' : 'Mostrar senha';
            toggleButton.setAttribute('aria-label', rotulo);
            toggleButton.title = rotulo;
        };

        if (passwordField) {
            setTimeout(function() {
                passwordField.type = 'password';
                passwordField.removeAttribute('disabled');
                atualizarControleSenha();
            }, 100);
        }

        if (toggleButton && passwordField) {
            toggleButton.addEventListener('click', function() {
                passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
                atualizarControleSenha();
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    class="astro-vvvwv3sm"
    ></script>
    
    <script src="<?php echo BASE_URL; ?>assets/js/color-modes.js"></script>
</body>
</html>
