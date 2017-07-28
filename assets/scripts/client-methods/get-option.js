import getValueById from './_getValueById';

export default (id, defaultValue) => {
    if(!id) {
        throw new Error('ID is required in getOption. Documentation is here - url');
    }

    return getValueById(id, defaultValue, false);
}