"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_select_value = id => {
        return Promise.resolve( {
            'type': 'select',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);