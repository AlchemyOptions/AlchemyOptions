"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_text_value = id => {
        return Promise.resolve( {
            'type': 'text',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);