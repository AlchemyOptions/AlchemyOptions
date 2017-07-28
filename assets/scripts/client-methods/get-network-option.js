import getValueById from './_getValueById';

export default (id, defaultValue) => {
    if(!id) {
        throw new Error('ID is required in getNetworkOption. Documentation is here - url');
    }

    return getValueById(id, defaultValue, true);
}