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
        text: mensagem,
        timer: 5000,
    });
}

function agendarAlertaSucesso(mensagem) {
    agendarAlerta({
        icon: 'success',
        text: mensagem,
        timer: 2000,
    });
}

async function confirmarExclusao(message, params=null) {
    const result = await Swal.fire({
        title: params?.title ?? 'Tem certeza?',
        text: message,
        icon: 'warning',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        showCancelButton: true,
        cancelButtonColor: '#6c757d',
        confirmButtonText: params?.confirmButtonText ?? 'Sim, remover!',
    })
    return result.isConfirmed;
}

async function confirmarSucesso(message, params=null) {
    const result = await Swal.fire({
        title: params?.title ?? 'Tem certeza?',
        text: message,
        icon: 'success',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: 'var(--bs-green)',
        showCancelButton: true,
        cancelButtonColor: '#6c757d',
        confirmButtonText: params?.confirmButtonText ?? 'Sim',
    })
    return result.isConfirmed;
}