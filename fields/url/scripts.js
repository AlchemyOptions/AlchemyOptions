"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_url_value = id => {
        return Promise.resolve( {
            'type': 'url',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);