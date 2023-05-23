function debounce(t, f) {
    let timeout = null
    return (...args) => {
        if (timeout) clearTimeout(timeout)
        timeout = setTimeout(f, t, ...args)
    }
}

function throttle(t, f) {
    let lastTime = null
    return (...args) => {
        const currTime = new Date().getTime()
        if (lastTime && currTime - lastTime < t) return
        lastTime = currTime
        f(...args)
    }
}