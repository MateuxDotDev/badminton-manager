const inputPesquisa = document.querySelector('#pesquisa');
const nenhumaEncontrada = document.querySelector('#nenhuma-competicao-encontrada');
const tabelaCompeticoes = document.querySelector('#tabela-competicoes');

const linhaCompeticao = new Map();
for (const tr of tabelaCompeticoes.rows) {
    const id = Number(tr.getAttribute('data-id'))
    linhaCompeticao.set(id, tr);
}

// algoritmo ineficiente
// mas ok enquanto o sistema não tiver muitas competições abertas a cada momento

/**
 * @param {array} termos
 * @param {string} texto
 */
function match(termos, texto) {
    for (const termo of termos) {
        if (texto.includes(termo)) {
            return true;
        }
    }
    return false;
}

/**
 * @param {array} termos
 */
function pesquisar(termos) {
    let algumaEncontrada = false; 
    for (const competicao of competicoes) {
        const ok = match(termos, competicao.nome) || match(termos, competicao.descricao);
        linha = linhaCompeticao.get(competicao.id);
        linha.style.display = ok ? 'table-row' : 'none';
        algumaEncontrada ||= ok;
    }
    tabelaCompeticoes.style.display = algumaEncontrada ? 'table' : 'none';
    nenhumaEncontrada.style.display = algumaEncontrada ? 'none' : 'block';
}

inputPesquisa.addEventListener('keydown', throttle(500, (evento) => {
    const termos = (inputPesquisa.value ?? '').split(/\s+/);
    pesquisar(termos)
}));