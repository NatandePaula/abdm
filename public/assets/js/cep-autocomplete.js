(() => {
    let callbackSequence = 0;

    const consultarViaCep = cep => new Promise((resolve, reject) => {
        const callback = `__abdmViaCep_${Date.now()}_${++callbackSequence}`;
        const script = document.createElement('script');
        let timeoutId;
        let finalizada = false;

        const limpar = () => {
            if (finalizada) return;
            finalizada = true;
            window.clearTimeout(timeoutId);
            delete window[callback];
            script.remove();
        };

        window[callback] = dados => {
            limpar();
            resolve(dados);
        };
        script.onerror = () => {
            limpar();
            reject(new Error('Não foi possível consultar o CEP.'));
        };
        script.src = `https://viacep.com.br/ws/${cep}/json/?callback=${callback}`;
        timeoutId = window.setTimeout(() => {
            limpar();
            reject(new Error('A consulta do CEP expirou.'));
        }, 12000);
        document.head.appendChild(script);
    });

    window.setupCepAutocomplete = (idCep, idRua, idCidade, idEstado, idFeedback = null) => {
        const cepInput = document.getElementById(idCep);
        const ruaInput = document.getElementById(idRua);
        const cidadeInput = document.getElementById(idCidade);
        const estadoInput = document.getElementById(idEstado);
        const feedback = idFeedback ? document.getElementById(idFeedback) : null;
        if (!cepInput || !ruaInput || !cidadeInput || !estadoInput) return;

        let ultimoCepConsultado = '';
        let geracaoConsulta = 0;

        const mostrarFeedback = mensagem => {
            if (!feedback) return;
            feedback.textContent = mensagem;
            feedback.classList.toggle('d-block', Boolean(mensagem));
        };

        const consultar = () => {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length === 0) return;
            if (cep.length !== 8) {
                cepInput.classList.add('is-invalid');
                cepInput.classList.remove('is-valid');
                mostrarFeedback('CEP deve ter 8 dígitos.');
                return;
            }
            if (cep === ultimoCepConsultado) return;

            ultimoCepConsultado = cep;
            const geracaoAtual = ++geracaoConsulta;
            cepInput.classList.remove('is-invalid', 'is-valid');
            mostrarFeedback('Consultando CEP...');

            consultarViaCep(cep)
                .then(dados => {
                    if (geracaoAtual !== geracaoConsulta || cepInput.value.replace(/\D/g, '') !== cep) return;
                    if (!dados || dados.erro) {
                        ultimoCepConsultado = '';
                        cepInput.classList.add('is-invalid');
                        mostrarFeedback('CEP não encontrado. Preencha o endereço manualmente.');
                        return;
                    }

                    cepInput.classList.remove('is-invalid');
                    cepInput.classList.add('is-valid');
                    ruaInput.value = dados.logradouro || '';
                    cidadeInput.value = dados.localidade || '';
                    estadoInput.value = dados.uf || '';
                    mostrarFeedback('');
                })
                .catch(() => {
                    if (geracaoAtual !== geracaoConsulta) return;
                    ultimoCepConsultado = '';
                    cepInput.classList.add('is-invalid');
                    mostrarFeedback('Não foi possível consultar o CEP. Preencha o endereço manualmente.');
                });
        };

        cepInput.addEventListener('input', function () {
            const cep = this.value.replace(/\D/g, '').slice(0, 8);
            this.value = cep.replace(/^(\d{5})(\d)/, '$1-$2');
            if (cep !== ultimoCepConsultado) {
                geracaoConsulta++;
                ultimoCepConsultado = '';
                this.classList.remove('is-valid', 'is-invalid');
                mostrarFeedback('');
            }
            if (cep.length === 8) consultar();
        });

        cepInput.addEventListener('blur', consultar);
    };
})();
