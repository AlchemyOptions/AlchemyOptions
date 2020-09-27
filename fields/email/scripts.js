"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_email_value = id => {
        return Promise.resolve( {
            'type': 'email',
            'id': id,
            'value': $(`#${id}`).val()
        } );
    };
})(window, jQuery);