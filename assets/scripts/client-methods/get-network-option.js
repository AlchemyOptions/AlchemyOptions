import askForData from "./_askForData";

export default id => {
    if(!id) {
        throw new Error('ID is required in getNetworkOption. Documentation is here - https://docs.alchemy-options.com/javascript/get_network_option.html');
    }

    return askForData({ id, type: 'getNetworkOption' } );
}