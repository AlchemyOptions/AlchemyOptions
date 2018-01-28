import askForData from "./_askForData";

export default id => {
    if(!id) {
        throw new Error('ID is required in getOption. Documentation is here - https://docs.alchemy-options.com/javascript/get_option.html');
    }

    return askForData({ id, type: 'getOption' } );
}