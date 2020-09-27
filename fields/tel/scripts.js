"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_tel_value = id => {
        return Promise.resolve( {
            'type': 'tel',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);