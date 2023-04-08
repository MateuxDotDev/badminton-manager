// TODO versões que aparecem no canto
// TODO talvez usar toastr no lugar

const $message = {
  warn: (message, title = 'Ops!') => Swal.fire({
    title: title,
    text: message,
    icon: 'warning',
    confirmButtonText: 'ok',
    confirmButtonColor: '#0d6efd'
  }),

  success: (message, title = 'Sucesso') => Swal.fire({
    title: title,
    text: message,
    icon: 'success',
    timer: 2000,
    showConfirmButton: false
  }),

  successOk: (message, title = 'Sucesso') => Swal.fire({
    title: title,
    text: message,
    icon: 'success',
    confirmButtonText: 'ok',
    confirmButtonColor: '#0d6efd'
  }),

  confirm: (message, title = 'Tem certeza?') => Swal.fire({
    title: title,
    text: message,
    icon: 'warning',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#d33',
    showCancelButton: true,
    cancelButtonColor: '#0d6efd',
    confirmButtonText: 'Sim, remover!'
  }),

  error: (message, title = 'Erro!') => Swal.fire({
    title: title,
    text: message,
    icon: 'error',
    confirmButtonText: 'ok',
    confirmButtonColor: '#0d6efd'
  })
}