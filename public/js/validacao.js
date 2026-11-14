/*
 * Validação dos formulários no navegador (cadastro de usuários e incidentes).
 * Serve só para ajudar quem está preenchendo: máscaras e avisos na hora.
 * As mesmas regras são conferidas de novo no servidor
 * (app/Http/Requests/UsuarioRequest.php e app/Http/Requests/ChamadoRequest.php).
 */
(function () {
    'use strict';

    const IDADE_MINIMA = 18;
    const IDADE_MAXIMA = 100;

    const numeros = (texto) => texto.replace(/\D/g, '');

    // ---------- Máscaras ----------
    const mascaras = {
        cpf(v) {
            v = numeros(v).slice(0, 11);
            return v.replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');
        },
        cep(v) {
            v = numeros(v).slice(0, 8);
            return v.replace(/^(\d{5})(\d)/, '$1-$2');
        },
        celular(v) {
            v = numeros(v).slice(0, 11);
            if (v.length > 7) return v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            if (v.length > 2) return v.replace(/^(\d{2})(\d+)/, '($1) $2');
            return v.replace(/^(\d+)/, '($1');
        },
    };

    // ---------- Regras (iguais às do servidor) ----------
    function cpfValido(cpf) {
        if (!/^\d{11}$/.test(cpf) || /^(\d)\1{10}$/.test(cpf)) return false;
        for (const tamanho of [9, 10]) {
            let soma = 0;
            for (let i = 0; i < tamanho; i++) soma += Number(cpf[i]) * (tamanho + 1 - i);
            const resto = soma % 11;
            const digito = resto < 2 ? 0 : 11 - resto;
            if (digito !== Number(cpf[tamanho])) return false;
        }
        return true;
    }

    function calcularIdade(dataIso) {
        const [ano, mes, dia] = dataIso.split('-').map(Number);
        const nascimento = new Date(ano, mes - 1, dia);
        // Recusa datas que não existem, como 31/02 (o JS "pula" para março).
        if (nascimento.getFullYear() !== ano || nascimento.getMonth() !== mes - 1 || nascimento.getDate() !== dia) {
            return null;
        }
        const hoje = new Date();
        let idade = hoje.getFullYear() - ano;
        if (hoje.getMonth() < mes - 1 || (hoje.getMonth() === mes - 1 && hoje.getDate() < dia)) idade--;
        return { idade, futura: nascimento > hoje };
    }

    const regras = {
        nome(campo) {
            const v = campo.value.trim().replace(/\s+/g, ' ');
            if (!v) return 'O campo nome é obrigatório.';
            if (v.length < 3) return 'O nome deve ter pelo menos 3 letras.';
            if (v.length > 100) return 'O nome pode ter no máximo 100 caracteres.';
            return '';
        },
        email(campo) {
            const v = campo.value.trim();
            if (!v) return 'O campo e-mail é obrigatório.';
            if (!/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i.test(v)) return 'Informe um e-mail válido, por exemplo: nome@empresa.com.';
            return '';
        },
        nascimento(campo) {
            const ajuda = campo.parentElement.querySelector('[data-idade]');
            if (ajuda) ajuda.textContent = 'Mínimo de 18 anos completos.';
            if (!campo.value) {
                return campo.validity.badInput
                    ? 'Informe uma data de nascimento que exista no calendário (dia/mês/ano).'
                    : 'O campo data de nascimento é obrigatório.';
            }
            const resultado = calcularIdade(campo.value);
            if (!resultado) return 'Informe uma data de nascimento que exista no calendário (dia/mês/ano).';
            if (resultado.futura) return 'A data de nascimento não pode estar no futuro.';
            if (ajuda) ajuda.textContent = `Idade: ${resultado.idade} anos.`;
            if (resultado.idade < IDADE_MINIMA) return `É preciso ter pelo menos ${IDADE_MINIMA} anos completos.`;
            if (resultado.idade > IDADE_MAXIMA) return `A idade máxima permitida é de ${IDADE_MAXIMA} anos. Confira o ano de nascimento.`;
            return '';
        },
        cpf(campo) {
            const v = numeros(campo.value);
            if (!campo.value.trim()) return 'O campo CPF é obrigatório.';
            if (v.length !== 11) return 'O CPF deve ter exatamente 11 números (pode digitar com ou sem pontos e traço).';
            if (!cpfValido(v)) return 'O CPF informado é inválido. Confira os números digitados.';
            return '';
        },
        celular(campo) {
            const v = numeros(campo.value);
            if (!campo.value.trim()) return 'O campo celular é obrigatório.';
            if (v.length !== 11) return 'O celular deve ter DDD + 9 números, por exemplo: (11) 98765-4321.';
            if (!/^[1-9]{2}9/.test(v)) return 'O número do celular deve começar com 9 logo depois do DDD.';
            return '';
        },
        cep(campo) {
            const v = numeros(campo.value);
            if (!campo.value.trim()) return 'O campo CEP é obrigatório.';
            if (v.length !== 8) return 'O CEP deve estar no formato 12345-678 ou 12345678, somente com números.';
            return '';
        },

        // ----- Incidente -----
        titulo(campo) {
            const v = campo.value.trim().replace(/\s+/g, ' ');
            if (!v) return 'O campo descrição resumida é obrigatório.';
            if (v.length < 3) return 'A descrição resumida deve ter pelo menos 3 caracteres.';
            if (v.length > 100) return 'A descrição resumida pode ter no máximo 100 caracteres.';
            return '';
        },
        descricao(campo) {
            const v = campo.value.trim();
            if (!v) return 'O campo descrição é obrigatório.';
            if (v.length < 10) return 'Explique melhor o problema: escreva pelo menos 10 caracteres.';
            if (v.length > 5000) return 'O campo descrição não pode ter mais de 5000 caracteres.';
            return '';
        },
        inicio(campo) {
            if (!campo.value) return 'O campo problema começou em é obrigatório.';
            if (!dataExiste(campo.value)) return 'Informe uma data de início que exista no calendário (dia/mês/ano).';
            if (campo.value > hojeIso()) return 'O problema não pode ter começado numa data futura.';
            return '';
        },
        prazo(campo) {
            const inicio = campo.form.querySelector('[data-validar="inicio"]');
            if (!campo.value) return 'O campo prazo para resolver é obrigatório.';
            if (!dataExiste(campo.value)) return 'Informe um prazo que exista no calendário (dia/mês/ano).';
            if (inicio && inicio.value && campo.value < inicio.value) {
                return 'Período inconsistente: o prazo não pode ser anterior à data em que o problema começou.';
            }
            if (campo.hasAttribute('data-novo') && campo.value < hojeIso()) {
                return 'O prazo para resolver não pode ser uma data que já passou.';
            }
            return '';
        },
    };

    function hojeIso() {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }

    function dataExiste(dataIso) {
        return calcularIdade(dataIso) !== null;
    }

    // ---------- Mostrar o resultado no campo ----------
    function validar(campo) {
        const erro = regras[campo.dataset.validar](campo);
        const aviso = campo.parentElement.querySelector('.invalid-feedback');
        campo.classList.toggle('is-invalid', erro !== '');
        campo.classList.toggle('is-valid', erro === '');
        if (aviso) aviso.textContent = erro;
        return erro === '';
    }

    document.querySelectorAll('form[data-validar-form]').forEach((form) => {
        const campos = form.querySelectorAll('[data-validar]');

        campos.forEach((campo) => {
            const tipo = campo.dataset.validar;

            if (mascaras[tipo]) {
                if (campo.value) campo.value = mascaras[tipo](campo.value);
                campo.addEventListener('input', () => { campo.value = mascaras[tipo](campo.value); });
            }

            // Valida ao sair do campo e, depois disso, a cada tecla.
            campo.addEventListener('blur', () => {
                if (tipo === 'nome' || tipo === 'email') campo.value = campo.value.trim().replace(/\s+/g, ' ');
                campo.dataset.tocado = '1';
                validar(campo);
            });
            const ehData = campo.type === 'date';
            campo.addEventListener(ehData ? 'change' : 'input', () => {
                if (campo.dataset.tocado || ehData) validar(campo);
            });

            if (campo.value && tipo === 'nascimento') validar(campo);
        });

        // Período: o prazo não pode começar antes do início.
        const inicio = form.querySelector('[data-validar="inicio"]');
        const prazo = form.querySelector('[data-validar="prazo"]');
        if (inicio && prazo) {
            const ajustarMinimo = () => {
                const hoje = hojeIso();
                prazo.min = prazo.hasAttribute('data-novo') && inicio.value < hoje ? hoje : inicio.value;
            };
            ajustarMinimo();
            inicio.addEventListener('change', () => {
                ajustarMinimo();
                if (prazo.value) validar(prazo);
            });
        }

        form.addEventListener('submit', (evento) => {
            let primeiroErro = null;
            campos.forEach((campo) => {
                if (!validar(campo) && !primeiroErro) primeiroErro = campo;
            });
            if (primeiroErro) {
                evento.preventDefault();
                primeiroErro.focus();
            }
        });
    });
})();
