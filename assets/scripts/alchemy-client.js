'use strict';

import getOption from './client-methods/get-option';
import getNetworkOption from './client-methods/get-network-option';
import getPostMeta from './client-methods/get-post-meta';

(function(win) {
    win.alchemyOptions = win.alchemyOptions || {};

    win.alchemyOptions.getOption = getOption;
    win.alchemyOptions.getNetworkOption = getNetworkOption;
    win.alchemyOptions.getPostMeta = getPostMeta;
})
(window);