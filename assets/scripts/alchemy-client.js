'use strict';

import getOption from './client-methods/get-option';

(win => {
    win.alchemyOptions = win.alchemyOptions || {};
    
    win.alchemyOptions.getOption = getOption;
})(window);