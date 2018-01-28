import q from 'kew';

export default (passedData) => {
    const alchemyClientData = window.alchemyOptionsClientData;

    if (!alchemyClientData) {
        throw new Error('alchemyOptionsClientData doesn\'t exist, cannot make a request.');
    }

    const xhr = new XMLHttpRequest();
    const defer = q.defer();
    const queryString = [];
    const data = {
        action: 'alchemy_options_client_request',
        nonce: alchemyClientData.nonce
    };

    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            queryString.push(`${key}=${data[key]}`);
        }
    }

    for (let key in passedData) {
        if (passedData.hasOwnProperty(key)) {
            queryString.push(`${key}=${passedData[key]}`);
        }
    }

    xhr.open("GET", `${alchemyClientData.adminURL}?${queryString.join('&')}`, true);

    xhr.onreadystatechange = () => {
        if (xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
            defer.resolve(xhr.response);
        }
    };

    xhr.onabort = xhr.onerror = () => {
        defer.reject(xhr.response);
    };

    xhr.send();

    return defer;
}