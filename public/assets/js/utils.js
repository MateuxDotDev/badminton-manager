/**
 * Filters an array of objects by a single key
 *
 * @param {Array} array  - The array to be filtered
 * @param {Array} keys   - The keys to be searched
 * @param {string} value - The value to be searched
 */
const filterByMultipleKeys = (array, keys, value) => {
    return array.filter(item => {
        return keys.some(key => {
            const itemValue = item[key];
            switch (typeof itemValue) {
                case "string":
                    return itemValue.toLowerCase().includes(value.toLowerCase());
                case "number":
                    return itemValue === Number(value);
                default:
                    return false;
            }
        });
    });
}

/**
 * Creates a timeout that will be cleared if the function is called again
 *
 * @param {number} t   - The timeout in milliseconds
 * @param {function} f - The function to be called
 * @returns {function} - The debounced function
 */
function debounce(t, f) {
    let timeout = null
    return (...args) => {
        if (timeout) clearTimeout(timeout)
        timeout = setTimeout(f, t, ...args)
    }
}

/**
 * Slows down the execution of a function
 *
 * @param {number} t   - The timeout in milliseconds
 * @param {function} f - The function to be called
 * @returns {function} - The throttled function
 */
function throttle(t, f) {
    let lastTime = null
    return (...args) => {
        const currTime = new Date().getTime()
        if (lastTime && currTime - lastTime < t) return
        lastTime = currTime
        f(...args)
    }
}

/**
 * Shortcut for document.querySelector
 * 
 * @param {string} s
 * @returns {Element|null}
 */
function qs(s) {
    return document.querySelector(s);
}

/**
 * Shortcut for document.querySelectorAll
 * 
 * @param {string} s
 * @returns {NodeList}
 */
function qsa(s) {
    return document.querySelectorAll(s);
}


/**
 * Shortcut for element.querySelector
 * 
 * @param {HTMLElement} e
 * @param {string} s
 * 
 * @returns {Element|null}
 */
function eqs(e, s) {
    return e.querySelector(s);
}


/**
 * Shortcut for element.querySelectorAll
 *
 * @param {HTMLElement} e
 * @param {string} s
 *
 * @returns {NodeList}
 */
function eqsa(e, s) {
    return e.querySelectorAll(s);
}


/**
 * Retorna ícone representando o sexo masculino
 * 
 * @returns {HTMLElement}
 */
function iconeMasculino() {
    const i = document.createElement('i');
    i.classList.add('bi', 'bi-gender-male', 'text-blue');
    i.title = 'Sexo masculino';
    return i;
}


/**
 * Retorna ícone representando o sexo feminino
 * 
 * @returns {HTMLElement}
 */
function iconeFeminino() {
    const i = document.createElement('i');
    i.classList.add('bi', 'bi-gender-female', 'text-pink');
    i.title = 'Sexo feminino';
    return i;
}

/**
 * Retorna ícone representando o sexo (caractere 'M' ou 'F') informado
 * 
 * @returns {HTMLElement|null}
 */
function iconeSexo(sexo) {
    if (!sexo || (typeof sexo != 'string')) return null;
    sexo = sexo.toUpperCase().substring(0, 1);
    if (sexo == 'M') return iconeMasculino();
    if (sexo == 'F') return iconeFeminino();
    return null;
}

/**
 * Formata data no padrão brasileiro
 * 
 * @param {Date} date
 * 
 * @returns {string}
 */
function dataBr(date) {
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth()+1).padStart(2, '0');
    const ano = String(date.getFullYear());
    return `${dia}/${mes}/${ano}`;
}

/**
 * Formata data no padrão ISO
 *
 * @param date
 * @returns {string}
 */
function brDateToYmd(date) {
    const partesData = date.split("/");
    return `${partesData[2]}-${partesData[1]}-${partesData[0]}`;
}


/**
 * Retorna a quantidade de dias faltando para a data informada
 *
 * @param {Date} dataFutura
 * @returns {number}
 */
function diasFaltando(dataFutura) {
    const diferencaEmMs = dataFutura.getTime() - new Date().getTime();
    return Math.ceil(diferencaEmMs / (1000 * 60 * 60 * 24));
}

/**
 * Retorna string em singular ou plural dependendo da quantidade
 * 
 * @param {number} qtd
 * @param {string} singular
 * @param {string} plural
 */
function pluralizar(qtd, singular, plural) {
    return qtd == 1 ? `${qtd} ${singular}` : `${qtd} ${plural}`;
}

/**
 * Remove todos os elementos filhos
 * 
 * @param {HTMLElement} e
 */
function esvaziar(e) {
    while (e.firstChild) e.firstChild.remove();
}

/**
 * Retorna a interseção entre dois arrays, com função
 * de comparação customizável.
 */
function intersectArrays(a, b, fn=null) {
    fn ??= (x, y) => x === y;
    return a.filter(x => b.some(y => fn(x, y)))
}

/**
 * Quebra um array em chunks de tamanho n
 * @param {Array} a
 * @param {number} n
 * @returns {Array}
 */
function arrayIntoChunks(a, n) {
    const ret = [];
    for (let i = 0; i < a.length; i += n) {
        ret.push(a.slice(i, i+n));
    }
    return ret;
}

/**
 * Busca as lista de categorias disponíveis
 */
async function fetchCategorias() {
    const emCache = localStorage.getItem("categorias");
    if (emCache) return JSON.parse(emCache);

    const response = await fetch('/categorias.php');
    const text = await response.text();

    let categorias = [];
    try {
        categorias = JSON.parse(text);
        localStorage.setItem("categorias", JSON.stringify(categorias));
    } catch (err) {
        console.error('Erro ao buscar a lista de categorias: ', { text, err });
    }

    return categorias;
}

/**
 * @param {HTMLElement} elemento
 * @param {string} texto
 *
 * @returns {void}
 */
function adicionarTooltip(elemento, texto) {
    texto = (texto ?? '').trim();
    if (texto.length === 0) return;
    elemento.setAttribute('title', texto);
    elemento.classList.add('contem-tooltip');
    new bootstrap.Tooltip(elemento);
}
