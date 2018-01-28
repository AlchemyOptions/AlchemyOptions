import askForData from "./_askForData";

export default (postID, metaID) => {
    if(!metaID || !postID) {
        throw new Error('metaID and postID are required in getPostMeta. Documentation is here - https://docs.alchemy-options.com/javascript/get_post_meta.html');
    }

    return askForData({ postID, metaID, type: 'getPostMeta' });
}