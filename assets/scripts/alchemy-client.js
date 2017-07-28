'use strict';

import getOption from './client-methods/get-option';
import getNetworkOption from './client-methods/get-network-option';

(function(win) {
    win.alchemyOptions = win.alchemyOptions || {};

    win.alchemyOptions.getOption = getOption;
    win.alchemyOptions.getNetworkOption = getNetworkOption;
})
(window);