const replaceKeyInString = (string, key, value) => string.replaceAll(`{{ ${key} }}`, value);

const filterBy = (array, key, value) => array.filter(item => item[key] === value);

const filterByMultipleKeys = (array, keys, value) => {
    return array.filter(item => {
        return keys.some(key => {
            const itemValue = item[key];
            if (typeof itemValue === "string") {
                return itemValue.toLowerCase().includes(value.toLowerCase());
            }
            return false;
        });
    });
}
