import getValueById from './_getValueById';

export default id => {
    if(!id) {
        throw new Error('ID is required in getOption. Documentation is here - url');
    }

    return getValueById(id, false);
}