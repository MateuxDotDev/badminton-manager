const _chave_alerta_storage = 'matchpoint-alerta-agendado';

{
    const json = sessionStorage.getItem(_chave_alerta_storage);
    if (json !== null) {
        try {
            const alerta = JSON.parse(json);
            Toast.fire(alerta);
        } catch (err) {
            console.error('Erro ao parsear JSON do alerta agendado');
        }
    }
    sessionStorage.removeItem(_chave_alerta_storage);
}

function agendarAlerta(alerta) {
    sessionStorage.setItem(_chave_alerta_storage, JSON.stringify(alerta));
}

function alertaErro(mensagem) {
    Toast.fire({
        icon: 'error',
        text: mensagem
    });
}

function agendarAlertaSucesso(mensagem) {
    agendarAlerta({
        icon: 'success',
        text: mensagem,
        timer: 2000,
    });
}

async function confirmarExclusao(message, title='Tem certeza?') {
    const result = await Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        showCancelButton: true,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, remover!'
    })
    return result.isConfirmed;
}