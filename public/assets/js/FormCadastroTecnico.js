class FormCadastroTecnico {

    #form;
    #inputs;

    // Seleção de clube
    #alertaDigitar;
    #containerExistentes;
    #opcaoDigitado;
    #timeoutPesquisa;

    constructor() {
        const form = FormCadastroTecnico.#template.content.firstElementChild.cloneNode(true);

        this.#inputs = {
            email: eqs(form, '[name=email]'),
            senha: eqs(form, '[name=senha]'),
            nome: eqs(form, '[name=nome]'),
            informacoes: eqs(form, '[name=informacoes]'),
            clube: eqs(form, '[name=clube]'),
        };

        this.#form = form;
        this.#configurarInputClube();
    }

    #configurarInputClube() {
        const form = this.#form;
        
        this.#alertaDigitar = eqs(form, '.alerta-digitar');
        const containerDigitado   = eqs(form, '.container-opcao-clube-digitado');
        this.#containerExistentes = eqs(form, '.container-opcoes-clubes');

        {
            const elem = this.#opcaoClube(0, '');
            this.#opcaoDigitado = {
                elem,
                input: eqs(elem, '.form-check-input'),
                label: eqs(elem, '.form-check-label'),
            };
        }

        containerDigitado.append(this.#opcaoDigitado.elem);

        this.#inputs.clube.addEventListener('keyup', () => {
            this.#handleInputClube();
        });
        this.#handleInputClube()
    }

    #opcaoClube(id, nome) {
        const template = FormCadastroTecnico.#templateOpcaoClube;

        const elem  = template.content.firstElementChild.cloneNode(true);
        const input = eqs(elem, '.form-check-input');
        const label = eqs(elem, '.form-check-label');

        input.name  = 'clube-radio';
        input.value = nome;

        const attrId = `clube-${id}`;
        input.setAttribute('id', attrId);
        label.setAttribute('for', attrId);

        label.innerText = nome;

        input.addEventListener('change', () => {
            if (input.checked) {
                this.#inputs.clube.value = (input.value ?? '').trim()
            }
        });

        return elem;
    }

    #getPesquisaClube() {
        return (this.#inputs.clube.value ?? '').trim();
    }

    #handleInputClube() {
        const value  = this.#getPesquisaClube();
        const termos = value.split(/\s+/);

        if (value == '') {
            this.#alertaDigitar.style.display = 'block';
            this.#opcaoDigitado.elem.style.display = 'none';
            esvaziar(this.#containerExistentes);
        } else {
            this.#alertaDigitar.style.display = 'none';
            this.#opcaoDigitado.elem.style.display = 'block';

            this.#opcaoDigitado.label.innerText = value;
            this.#opcaoDigitado.input.value = value;
            this.#opcaoDigitado.input.checked = true;

            if (this.#timeoutPesquisa) {
                clearTimeout(this.#timeoutPesquisa);
            }

            this.#timeoutPesquisa = setTimeout(() => {
                this.#pesquisarClubes(termos, (clubes) => {
                    // caso o usuário faça uma pesquisa mas apague ela antes de termos buscado,
                    // para evitar que os resultados apareçam
                    if (this.#getPesquisaClube() == '') return;
                    if (!clubes) return;

                    esvaziar(this.#containerExistentes);
                    for (const clube of clubes) {
                        if (clube.nome.toUpperCase() == value.toUpperCase()) continue;
                        this.#containerExistentes.append(this.#opcaoClube(clube.id, clube.nome));
                    }

                })
            }, 200);
        }
    }

    #pesquisarClubes(termos, callback) {
        if (termos.length == 0 || (termos.length == 1 && termos[0] == '')) {
            callback([]);
            return;
        }

        const baseURL = new URL(location).origin;
        const url = new URL('cadastro/acao.php', baseURL);
        url.searchParams.append('acao', 'pesquisarClubes');
        for (const termo of termos) {
            url.searchParams.append('termos[]', termo)
        }

        fetch(url)
        .then(r => r.text())
        .then(JSON.parse)
        .then(o => o.resultados)
        .then(callback)
        .catch(console.error);
    }

    get elemento() {
        return this.#form;
    }

    get valores() {
        const vals = {};
        for (const [name, input] of Object.entries(this.#inputs)) {
            vals[name] = input.value;
        }
        return vals;
    }


    static #template = Object.assign(document.createElement('template'), {
        innerHTML: `
        <form>
            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" class="form-control" name="email" required/>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input type="password" class="form-control" name="senha" required/>
            </div>
            <div class="mb-3">
                <label class="form-label">Nome completo</label>
                <input type="text" class="form-control" name="nome" required/>
            </div>
            <div class="mb-3">
                <label class="form-label">Informações de contato</label>
                <textarea class="form-control" name="informacoes"></textarea>
            </div>
            <div class="mb-5">
                <div class="mb-3">
                    <label class="form-label">Clube</label>
                    <input type="text" class="form-control" name="clube" autocomplete="off"/>
                </div>
                <div class="card">
                    <div class="card-header">Clubes encontrados</div>
                    <div class="card-body">
                        <div class="alerta-digitar alert alert-info mb-0">
                            Digite o nome de um clube.
                        </div>


                        <div class="container-opcao-clube-digitado">
                        </div>
                        
                        <div class="container-opcoes-clubes">
                        </div>

                    </div>
                </div>
            </div>
        </form>`
    });

    static #templateOpcaoClube = Object.assign(document.createElement('template'), {
        innerHTML: `
        <div class="form-check">
            <input type="radio" class="form-check-input">
            <label class="form-check-label"></label>
        </div> `
    });

}