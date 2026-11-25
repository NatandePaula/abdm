<script src="<?= BASE_URL ?>assets/dist/js/bootstrap.bundle.min.js"></script>


<script>
    // === CONFIGURAÇÃO DE TEMPO (Puxa valor do PHP) ===
    const INACTIVITY_LIMIT_SEC = <?php echo TEMPO_LIMITE_INATIVIDADE; ?>;
    const WARNING_TIME_SEC = <?php echo TEMPO_AVISO_PREVIO; ?>; 
    const WARNING_POINT_SEC = INACTIVITY_LIMIT_SEC - WARNING_TIME_SEC; 
    
    const RENEWAL_ENDPOINT = '<?= BASE_URL ?>dao/controleTempo.php';

    let displayTimerInterval; 
    let sessionTimerId;       
    let countdownId;          
    let startTime;            
    
    function forcarEncerramentoSessao() {
        const formulario = document.getElementById('formEncerrarSessao');
        if (formulario) {
            formulario.submit();
        } else {
            window.location.href = '<?= BASE_URL ?>index.php?timeout=1';
        }
    }

    function iniciarContagemRegressiva(seconds) {
        let count = seconds;
        const countdownElement = document.getElementById('countdown');
        if (countdownElement) { countdownElement.textContent = count; }

        countdownId = setInterval(() => {
            count--;
            if (countdownElement) { countdownElement.textContent = count; }

            if (count <= 0) {
                clearInterval(countdownId);
                forcarEncerramentoSessao(); 
            }
        }, 1000);
    }

    function exibirModalAviso() {
        var modalEl = document.getElementById('sessionWarningModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
        iniciarContagemRegressiva(WARNING_TIME_SEC); 
    }
    
    function atualizarTemporizadorExibido() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const remaining = INACTIVITY_LIMIT_SEC - elapsed;
        const displayElements = document.querySelectorAll('[data-session-timer]');
        
        if (remaining >= 0) {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            displayElements.forEach(displayElement => {
                displayElement.textContent = formattedTime;
                
                if (remaining <= WARNING_POINT_SEC) {
                    displayElement.style.color = 'red';
                } else {
                    displayElement.style.color = 'inherit';
                }
            });
        } else {
            displayElements.forEach(displayElement => {
                displayElement.textContent = '00:00';
            });
            clearInterval(displayTimerInterval);
        }
    }
    
    function iniciarTemporizadorExibido() {
        startTime = Date.now();
        clearInterval(displayTimerInterval);
        displayTimerInterval = setInterval(atualizarTemporizadorExibido, 1000);
        atualizarTemporizadorExibido(); 
    }

    function reiniciarTemporizador() {
        clearTimeout(sessionTimerId); 
        clearInterval(countdownId); 
        
        sessionTimerId = setTimeout(exibirModalAviso, WARNING_POINT_SEC * 1000);
        
        iniciarTemporizadorExibido();
    }
    
    // Renova a sessão quando a pessoa escolhe continuar conectada.
    function manterSessaoAtiva() {
        fetch(RENEWAL_ENDPOINT, {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        })
            .then(response => {
                if (response.ok) {
                    var modalEl = document.getElementById('sessionWarningModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { modal.hide(); }
                    
                    reiniciarTemporizador();
                } else {
                    console.error('Erro na resposta do servidor:', response.status, response.statusText);
                }
            })
            .catch(error => {
                console.error('Erro de rede/AJAX ao renovar a sessão:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnKeepAlive = document.getElementById('btnKeepAlive');
        if (btnKeepAlive) {
            btnKeepAlive.addEventListener('click', manterSessaoAtiva);
        }
        
        ['click', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(eventType => {
            document.body.addEventListener(eventType, reiniciarTemporizador, true);
        });
        
        reiniciarTemporizador(); 

        function ajustarAlturaAreaTexto(el) {
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight + 2) + 'px';
        }

        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.style.overflowY = 'hidden'; // Evita a barra de rolagem
            textarea.addEventListener('input', () => ajustarAlturaAreaTexto(textarea));
            ajustarAlturaAreaTexto(textarea);
        });
    });

</script>

<div class="modal fade" id="sessionWarningModal" tabindex="-1" aria-labelledby="sessionWarningLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionWarningLabel">Aviso de Inatividade</h5>
            </div>
            <div class="modal-body">
                Sua sessão será encerrada em <span id="countdown"><?php echo TEMPO_AVISO_PREVIO; ?></span> segundos por inatividade. 
                <p>Deseja continuar logado?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="forcarEncerramentoSessao()">Sair Agora</button>
                <button type="button" class="btn btn-primary" id="btnKeepAlive">Continuar Logado</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/pdf-preview.js?v=<?php echo filemtime(ROOT_DIR . '/public/assets/js/pdf-preview.js'); ?>" data-viewer-url="<?php echo htmlspecialchars(BASE_URL . 'assets/pdfjs/web/viewer.html?v=' . filemtime(ROOT_DIR . '/public/assets/pdfjs/web/viewer.html'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
