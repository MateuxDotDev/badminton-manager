document.querySelectorAll('[data-click-switch]').forEach(elem => {
    const stateText = {
        0: elem.innerText,
        1: elem.getAttribute('data-click-switch')
    };
    elem.setAttribute('data-state', 0);
    const cooldownMs = 400;
    elem.addEventListener('click', throttle(cooldownMs, () => {
        const state = elem.getAttribute('data-state');
        const newState = state == 0 ? 1 : 0;
        elem.setAttribute('data-state', newState);
        elem.innerText = stateText[newState];
    }));
});

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(elem => {
    console.log('ué mano', elem)
    new bootstrap.Tooltip(elem);
});