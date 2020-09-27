"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_checkbox_value = id => {
        return Promise.resolve( {
            'type': 'checkbox',
            'id': id,
            'value': $(`#${id}`).find(':checked').map((i, checkbox) => {
                return $(checkbox).data('value');
            }).get()
        } );
    };
})(window, jQuery);