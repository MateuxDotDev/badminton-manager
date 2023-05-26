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
