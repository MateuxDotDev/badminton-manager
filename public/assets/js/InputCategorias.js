class InputCategorias
{
    static #templateItem = Object.assign(document.createElement('template'), {
        innerHTML: `
            <div class="form-check">
                <input checked class="cat-input form-check-input">
                <label class="cat-label form-check-label"></label>
            </div>
        `
    });

    static #template = Object.assign(document.createElement('template'), {
        innerHTML: `
            <div>
                <span class="form-label d-flex flex-row gap-3 align-items-center">
                    <span class="cat-label"></span>
                    <button class="cat-btn-marcar-todas btn btn-link btn-sm" title="Marcar todas">
                        <i class="bi bi-check-square-fill fs-5"></i>
                    </button>
                    <button class="cat-btn-desmarcar-todas btn btn-link btn-sm" title="Desmarcar todas">
                        <i class="bi bi-x-square fs-5"></i>
                    </button>
                </span>
                <div class="cat-itens d-flex flex-row gap-5">
                </div>
            </div>
        `
    });

    // Para distinguir os elementos quando tem mais de um na mesma página
    static #counter = 0;

    #inputs = new Map();

    #elem;

    #criarItem(categoria, name, radio) {
        const elem = InputCategorias.#templateItem.content.firstElementChild.cloneNode(true);

        const atributoId = `cat-${InputCategorias.#counter}-${categoria.id}`;

        const input = eqs(elem, '.cat-input');
        input.setAttribute('id', atributoId);
        input.setAttribute('value', categoria.id);
        input.setAttribute('name', name);
        input.setAttribute('type', radio ? 'radio' : 'checkbox');

        const label = eqs(elem, '.cat-label');
        label.setAttribute('for', atributoId);
        label.innerText = categoria.descricao;

        this.#inputs.set(categoria.id, input);

        return elem;
    }

    constructor(categorias, opcoes={}) {
        InputCategorias.#counter++;

        opcoes = Object.assign({
            label: 'Categorias',
            botoes: true,
            radio: false,
        }, opcoes);

        const elem = InputCategorias.#template.content.firstElementChild.cloneNode(true);

        eqs(elem, '.cat-label').innerText = opcoes.label;

        const container = eqs(elem, '.cat-itens');

        const name = `cat-${InputCategorias.#counter}`;

        const itens = categorias.map(c => this.#criarItem(c, name, opcoes.radio));
        for (const chunk of arrayIntoChunks(itens, 7)) {
            const div = document.createElement('div');
            div.append(...chunk);
            container.append(div);
        }

        const btnMarcar = eqs(elem, '.cat-btn-marcar-todas');
        const btnDesmarcar = eqs(elem, '.cat-btn-desmarcar-todas');

        if (opcoes.botoes) {
            btnMarcar.addEventListener('click', () => {
                this.marcarTodas();
            });
            btnDesmarcar.addEventListener('click', () => {
                this.desmarcarTodas();
            });
        } else {
            btnMarcar.remove();
            btnDesmarcar.remove();
        }


        this.#elem = elem;
    }

    elemento() {
        return this.#elem;
    }

    get marcadas() {
        const a = [];
        for (const [id, item] of this.#inputs.entries()) {
            if (item.checked) {
                a.push(id);
            }
        }
        return a;
    }

    set marcadas(a) {
        for (const [id, item] of this.#inputs.entries()) {
            item.checked = a.includes(id);
        }
    }

    marcar(id) {
        const item = this.#inputs.get(id);
        if (item) item.checked = true;
    }

    desmarcar(id) {
        const item = this.#inputs.get(id);
        if (item) item.checked = false;
    }

    isMarcada(id) {
        return this.#inputs.get(id)?.checked ?? false;
    }

    marcarTodas() {
        for (const item of this.#inputs.values()) {
            item.checked = true;
        }
    }

    desmarcarTodas() {
        for (const item of this.#inputs.values()) {
            item.checked = false;
        }
    }

    habilitar(id) {
        const item = this.#inputs.get(id);
        if (item) item.removeAttribute('disabled');
    }

    desabilitar(id) {
        const item = this.#inputs.get(id);
        if (item) {
            item.checked = false;
            item.setAttribute('disabled', '');
        }
    }

    habilitarTodas() {
        for (const item of this.#inputs.values()) {
            item.removeAttribute('disabled');
        }
    }

    desabilitarTodas() {
        this.habilitadas = [];
    }

    set habilitadas(a) {
        for (const [id, item] of this.#inputs.entries()) {
            if (a.includes(id)) {
                item.removeAttribute('disabled');
            } else {
                item.checked = false;
                item.setAttribute('disabled', '');
            }
        }
    }
}