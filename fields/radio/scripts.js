"use strict";

(function(window, $) {
    window.AO = window.AO || {};

    AO.get_radio_value = id => {
        return Promise.resolve( {
            'type': 'radio',
            'id': id,
            'value': $(`#${id}`).find(':checked').data('value')
        } );
    };
})(window, jQuery);